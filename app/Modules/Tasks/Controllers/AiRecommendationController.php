<?php

namespace ProjectsManager\Modules\Tasks\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ProjectsManager\AiAssigneeRecommendation;
use ProjectsManager\Http\Controllers\Controller;
use ProjectsManager\Modules\Tasks\Handlers\UpdateTaskHandler;
use ProjectsManager\Services\AiRecommendationService;
use ProjectsManager\Task;

class AiRecommendationController extends Controller
{
    public function __construct(
        private readonly AiRecommendationService $aiRecommendationService,
        private readonly UpdateTaskHandler $updateTaskHandler
    ) {
    }

    /**
     * Get AI recommendations for task assignee.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function recommend(Request $request): JsonResponse
    {
        if ($request->input('project_id') === '' || $request->input('project_id') === 'null') {
            $request->merge(['project_id' => null]);
        }
        if ($request->input('task_id') === '' || $request->input('task_id') === 'null') {
            $request->merge(['task_id' => null]);
        }

        $validated = $request->validate([
            'name' => ['nullable', 'string', 'max:255'],
            'summary' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'issue_type' => ['nullable', 'string', 'max:50'],
            'priority' => ['nullable', 'string', 'max:50'],
            'task_id' => ['nullable', 'integer', 'exists:tasks,id'],
        ]);

        $recommendations = $this->aiRecommendationService->recommend(
            $validated,
            $validated['project_id'] ?? null,
            5
        );

        return response()->json([
            'success' => true,
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * Recommend assignees for many unassigned tasks in one request.
     */
    public function recommendBatch(Request $request): JsonResponse
    {
        @set_time_limit(300);

        $validated = $request->validate([
            'task_ids' => ['required', 'array', 'min:1'],
            'task_ids.*' => ['integer', 'exists:tasks,id'],
        ]);

        $tasks = Task::query()
            ->whereIn('id', $validated['task_ids'])
            ->whereNull('assignee_id')
            ->whereNotIn('status', ['done', 'closed', 'completed', 'resolved'])
            ->get();

        $items = $this->aiRecommendationService->recommendMany($tasks, 3);

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }

    /**
     * Apply a batch of AI assignee suggestions.
     */
    public function assignBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'assignments' => ['required', 'array', 'min:1'],
            'assignments.*.task_id' => ['required', 'integer', 'exists:tasks,id'],
            'assignments.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'assignments.*.score' => ['nullable', 'numeric'],
            'assignments.*.match_reasons' => ['nullable', 'array'],
        ]);

        $updated = [];
        foreach ($validated['assignments'] as $row) {
            $task = Task::find($row['task_id']);
            if (!$task) {
                continue;
            }

            $this->updateTaskHandler->handle($task, ['assignee_id' => $row['user_id']]);

            try {
                AiAssigneeRecommendation::create([
                    'task_id' => $task->id,
                    'user_id' => $row['user_id'],
                    'score' => $row['score'] ?? 0,
                    'rank_position' => 1,
                    'match_reasons' => $row['match_reasons'] ?? ['Được gợi ý bởi mô hình AI'],
                    'status' => 'accepted',
                ]);
            } catch (\Exception $e) {
                // Keep assignment even if the log row fails.
            }

            $updated[] = $task->fresh(['project', 'assignee', 'reporter']);
        }

        return response()->json([
            'success' => true,
            'tasks' => $updated,
        ]);
    }

    /**
     * Record feedback on an AI recommendation.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function feedback(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'recommendation_id' => ['required', 'integer', 'exists:ai_assignee_recommendations,id'],
            'status' => ['required', 'string', 'in:accepted,rejected,changed'],
        ]);

        $recommendation = AiAssigneeRecommendation::find($validated['recommendation_id']);
        if ($recommendation) {
            $recommendation->update(['status' => $validated['status']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Unassign AI test-pool tasks so recommendations can be tried again.
     */
    public function resetTestAssignments(Request $request): JsonResponse
    {
        if ($request->input('project_id') === '' || $request->input('project_id') === 'null') {
            $request->merge(['project_id' => null]);
        }

        $validated = $request->validate([
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'task_ids' => ['nullable', 'array'],
            'task_ids.*' => ['integer', 'exists:tasks,id'],
        ]);

        $query = Task::query()->where('labels', 'like', '%ai-test%');
        if (!empty($validated['project_id'])) {
            $query->where('project_id', $validated['project_id']);
        }
        if (!empty($validated['task_ids'])) {
            $query->whereIn('id', $validated['task_ids']);
        }

        $updated = [];
        foreach ($query->get() as $task) {
            $this->updateTaskHandler->handle($task, ['assignee_id' => null]);
            $updated[] = $task->fresh(['project', 'assignee', 'reporter']);
        }

        return response()->json([
            'success' => true,
            'reset_count' => count($updated),
            'tasks' => $updated,
        ]);
    }
}
