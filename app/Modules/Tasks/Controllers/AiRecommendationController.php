<?php

namespace ProjectsManager\Modules\Tasks\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ProjectsManager\User;
use ProjectsManager\AiAssigneeRecommendation;
use ProjectsManager\Http\Controllers\Controller;
use ProjectsManager\Modules\Tasks\Handlers\UpdateTaskHandler;
use ProjectsManager\Services\AiRecommendationService;
use ProjectsManager\Task;

class AiRecommendationController extends Controller
{
    /**
     * Create the AI recommendation controller.
     *
     * @param AiRecommendationService $aiRecommendationService
     * @param UpdateTaskHandler $updateTaskHandler
     *
     * @return void
     */
    public function __construct(
        private readonly AiRecommendationService $aiRecommendationService,
        private readonly UpdateTaskHandler $updateTaskHandler
    ) {
    }

    /**
     * Get AI recommendations for task assignee.
     *
     * @param Request $request
     *
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

        $task = !empty($validated['task_id'])
            ? Task::findOrFail($validated['task_id'])
            : new Task(['project_id' => $validated['project_id'] ?? null, 'user_id' => $request->user()->id]);
        $this->authorize('assign', $task);
        if ($task->exists) {
            abort_if(isset($validated['project_id']) && (int) $validated['project_id'] !== (int) $task->project_id, 422, 'Task does not belong to this project.');
            $validated['project_id'] = $task->project_id;
        }

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
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function recommendBatch(Request $request): JsonResponse
    {
        @set_time_limit(300);

        $validated = $request->validate([
            'task_ids' => ['required', 'array', 'min:1', 'max:25'],
            'task_ids.*' => ['integer', 'distinct', 'exists:tasks,id'],
        ]);

        $tasks = Task::query()
            ->whereIn('id', $validated['task_ids'])
            ->get();

        foreach ($tasks as $task) {
            $this->authorize('assign', $task);
        }
        $tasks = $tasks->filter(fn (Task $task) => !$task->assignee_id && !in_array($task->status, ['done', 'closed', 'completed', 'resolved'], true));

        $items = $this->aiRecommendationService->recommendMany($tasks, 3);

        return response()->json([
            'success' => true,
            'items' => $items,
        ]);
    }

    /**
     * Apply a batch of AI assignee suggestions.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function assignBatch(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'assignments' => ['required', 'array', 'min:1', 'max:25'],
            'assignments.*.task_id' => ['required', 'integer', 'distinct', 'exists:tasks,id'],
            'assignments.*.user_id' => ['required', 'integer', 'exists:users,id'],
            'assignments.*.score' => ['nullable', 'numeric', 'between:0,1'],
            'assignments.*.match_reasons' => ['nullable', 'array', 'max:10'],
            'assignments.*.match_reasons.*' => ['string', 'max:500'],
        ]);

        $updated = DB::transaction(function () use ($validated) {
            $tasks = Task::whereIn('id', array_column($validated['assignments'], 'task_id'))
                ->orderBy('id')->lockForUpdate()->get()->keyBy('id');
            foreach ($validated['assignments'] as $row) {
                $task = $tasks->get($row['task_id']);
                abort_unless($task, 404);
                $this->authorize('assign', $task);
                abort_if($task->assignee_id || in_array($task->status, ['done', 'closed', 'completed', 'resolved'], true), 409, 'Task is no longer available for assignment.');
                $candidate = User::where('id', $row['user_id'])->where('status', 'active')->first();
                abort_unless($candidate && (!$task->project_id || $task->project->users()->where('users.id', $candidate->id)->exists()), 422, 'Assignee must be an active project member.');
            }

            $updated = [];
            foreach ($validated['assignments'] as $row) {
                $task = $tasks->get($row['task_id']);
                $this->updateTaskHandler->handle($task, ['assignee_id' => $row['user_id']]);
                AiAssigneeRecommendation::create([
                    'task_id' => $task->id,
                    'user_id' => $row['user_id'],
                    'score' => $row['score'] ?? 0,
                    'rank_position' => 1,
                    'match_reasons' => $row['match_reasons'] ?? [],
                    'status' => 'accepted',
                ]);
                $updated[] = $task->fresh(['project', 'assignee', 'reporter']);
            }

            return $updated;
        });

        return response()->json(['success' => true, 'tasks' => $updated]);
    }

    /**
     * Record feedback on an AI recommendation.
     *
     * @param Request $request
     *
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
            $this->authorize('assign', Task::findOrFail($recommendation->task_id));
            $recommendation->update(['status' => $validated['status']]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Unassign AI test-pool tasks so recommendations can be tried again.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function resetTestAssignments(Request $request): JsonResponse
    {
        if ($request->input('project_id') === '' || $request->input('project_id') === 'null') {
            $request->merge(['project_id' => null]);
        }

        $validated = $request->validate([
            'project_id' => ['nullable', 'integer', 'exists:projects,id'],
            'task_ids' => ['nullable', 'array'],
            'task_ids.*' => ['integer', 'distinct', 'exists:tasks,id'],
        ]);

        $query = Task::query()->where('labels', 'like', '%ai-test%');
        if (!empty($validated['project_id'])) {
            $query->where('project_id', $validated['project_id']);
        }
        if (!empty($validated['task_ids'])) {
            $query->whereIn('id', $validated['task_ids']);
        }

        $updated = DB::transaction(function () use ($query) {
            $tasks = $query->orderBy('id')->lockForUpdate()->get();
            foreach ($tasks as $task) {
                $this->authorize('assign', $task);
            }
            $updated = [];
            foreach ($tasks as $task) {
                $this->updateTaskHandler->handle($task, ['assignee_id' => null]);
                $updated[] = $task->fresh(['project', 'assignee', 'reporter']);
            }
            return $updated;
        });

        return response()->json([
            'success' => true,
            'reset_count' => count($updated),
            'tasks' => $updated,
        ]);
    }
}
