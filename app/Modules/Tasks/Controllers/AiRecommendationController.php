<?php

namespace ProjectsManager\Modules\Tasks\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ProjectsManager\AiAssigneeRecommendation;
use ProjectsManager\Http\Controllers\Controller;
use ProjectsManager\Services\AiRecommendationService;

class AiRecommendationController extends Controller
{
    public function __construct(
        private readonly AiRecommendationService $aiRecommendationService
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
}
