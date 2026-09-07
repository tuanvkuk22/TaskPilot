<?php

namespace ProjectsManager\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use ProjectsManager\AiAssigneeRecommendation;
use ProjectsManager\Project;
use ProjectsManager\Task;
use ProjectsManager\User;

class AiRecommendationService
{
    protected $projectHistoryCache = [];
    /**
     * Recommend assignees for a given task context.
     *
     * @param array $taskData
     * @param int|null $projectId
     * @param int $topK
     * @return array
     */
    public function recommend(array $taskData, ?int $projectId = null, int $topK = 5): array
    {
        $projectId = $projectId ?? $taskData['project_id'] ?? null;
        $candidateUsers = $this->getCandidateUsers($projectId);

        return $this->recommendWithCandidates($taskData, $candidateUsers, $projectId, $topK, true);
    }

    /**
     * Recommend assignees for many existing tasks, reusing candidates per project.
     *
     * @param iterable $tasks
     * @param int $topK
     * @return array
     */
    public function recommendMany(iterable $tasks, int $topK = 3): array
    {
        $candidateCache = [];
        $items = [];

        foreach ($tasks as $task) {
            $projectId = $task->project_id ? (int) $task->project_id : null;
            $cacheKey = $projectId ?? 0;
            if (!isset($candidateCache[$cacheKey])) {
                $candidateCache[$cacheKey] = $this->getCandidateUsers($projectId);
            }

            $recommendations = $this->recommendWithCandidates([
                'name' => $task->name,
                'summary' => $task->name,
                'description' => $task->description,
                'issue_type' => $task->issue_type ?? 'task',
                'priority' => $task->priority ?? 'medium',
                'labels' => $task->labels ?? '',
                'task_id' => $task->id,
                'project_id' => $projectId,
            ], $candidateCache[$cacheKey], $projectId, $topK, false);

            $items[] = [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'issue_type' => $task->issue_type,
                'priority' => $task->priority,
                'project_id' => $projectId,
                'recommendations' => $recommendations,
                'suggested' => $recommendations[0] ?? null,
            ];
        }

        return $items;
    }

    /**
     * Run inference against a prepared candidate set.
     */
    protected function recommendWithCandidates(
        array $taskData,
        $candidateUsers,
        ?int $projectId,
        int $topK,
        bool $persist
    ): array {
        if ($candidateUsers->isEmpty()) {
            return [];
        }

        $formattedCandidates = $candidateUsers->map(function (User $user) {
            $skills = $user->skills ? $user->skills->pluck('skill_name')->toArray() : [];
            $activeTasksCount = Task::where('assignee_id', $user->id)
                ->whereNotIn('status', ['done', 'closed', 'completed'])
                ->count();

            return [
                'id' => $user->id,
                'name' => $user->full_name ?? $user->name,
                'username' => $user->username ?? $user->email,
                'email' => $user->email,
                'job_title' => $user->job_title ?? '',
                'department' => $user->department ?? '',
                'status' => $user->status ?? 'active',
                'skills' => $skills,
                'active_tasks_count' => $activeTasksCount,
            ];
        })->toArray();

        $projectName = '';
        $projectCode = '';
        $history = [];
        $components = [];
        if ($projectId) {
            $project = Project::find($projectId);
            if ($project) {
                $projectName = $project->name;
                $projectCode = $project->project_code ?: $project->name;
            }

            $historyTasks = $this->historyTasksForProject($projectId);
            foreach ($historyTasks as $historyTask) {
                if (!empty($taskData['task_id']) && (int) $historyTask->id === (int) $taskData['task_id']) {
                    continue;
                }
                $history[] = [
                    'summary' => $historyTask->name,
                    'assignee' => $historyTask->assignee->username ?? $historyTask->assignee->email ?? '',
                    'username' => $historyTask->assignee->username ?? '',
                    'issuetype' => $historyTask->issue_type ?? 'task',
                    'components' => $historyTask->skills ? $historyTask->skills->pluck('skill_name')->implode(' ') : '',
                ];
            }
        }

        if (!empty($taskData['task_id'])) {
            $current = Task::with('skills')->find($taskData['task_id']);
            if ($current && $current->skills) {
                $components = $current->skills->pluck('skill_name')->toArray();
            }
        }

        $payload = [
            'summary' => $taskData['name'] ?? $taskData['summary'] ?? '',
            'description' => $taskData['description'] ?? '',
            'issuetype' => $taskData['issue_type'] ?? 'task',
            'priority' => $taskData['priority'] ?? 'medium',
            'projectname' => $projectCode ?: $projectName,
            'components' => implode(' ', $components),
            'labels' => $taskData['labels'] ?? '',
            'reporter' => auth()->user()?->username ?? auth()->user()?->email ?? '',
            'candidate_users' => $formattedCandidates,
            'history' => $history,
        ];

        $recommendations = $this->runPythonInference($payload, $topK);

        $results = [];
        foreach ($recommendations as $index => $item) {
            if (!is_array($item)) {
                continue;
            }

            $matchedUser = $this->matchCandidateUser($candidateUsers, $item);
            $formatted = $this->formatRecommendationForUi($item, $matchedUser, $index + 1);
            if ($formatted === null) {
                continue;
            }

            if ($persist && !empty($taskData['task_id'])) {
                try {
                    $record = AiAssigneeRecommendation::create([
                        'task_id' => $taskData['task_id'],
                        'user_id' => $formatted['user_id'],
                        'score' => $formatted['score'],
                        'rank_position' => $formatted['rank_position'],
                        'match_reasons' => $formatted['match_reasons'],
                        'status' => 'pending',
                    ]);
                    $formatted['recommendation_id'] = $record->id;
                } catch (Exception $e) {
                    Log::warning("Failed to store AI recommendation log: " . $e->getMessage());
                }
            }

            $results[] = $formatted;
        }

        return $results;
    }

    /**
     * Match a Python recommendation row back to a TaskPilot user.
     */
    protected function matchCandidateUser($candidateUsers, array $item)
    {
        $userId = $item['user_id'] ?? null;
        $keys = array_values(array_filter([
            isset($item['assignee']) ? (string) $item['assignee'] : null,
            isset($item['username']) ? (string) $item['username'] : null,
            isset($item['email']) ? (string) $item['email'] : null,
            isset($item['name']) ? (string) $item['name'] : null,
            isset($item['display_name']) ? (string) $item['display_name'] : null,
        ], static fn ($value) => $value !== null && $value !== ''));

        return $candidateUsers->first(function ($user) use ($userId, $keys) {
            if ($userId !== null && $userId !== '' && (string) $user->id === (string) $userId) {
                return true;
            }
            return in_array((string) ($user->username ?? ''), $keys, true)
                || in_array((string) ($user->email ?? ''), $keys, true)
                || in_array((string) $user->id, $keys, true);
        });
    }

    /**
     * Shape a recommendation for TaskCreate.vue (name, user_id, job_title, ...).
     */
    protected function formatRecommendationForUi(array $item, $matchedUser, int $rank): ?array
    {
        $score = floatval($item['score'] ?? 0);
        $name = $item['name'] ?? $item['display_name'] ?? null;
        $jobTitle = $item['job_title'] ?? '';
        $activeTasks = array_key_exists('active_tasks_count', $item)
            ? intval($item['active_tasks_count'])
            : null;
        $confidence = array_key_exists('confidence_percent', $item)
            ? intval($item['confidence_percent'])
            : null;
        $userId = $item['user_id'] ?? null;
        $matchReasons = $item['match_reasons'] ?? ['Được gợi ý bởi mô hình AI'];

        $username = $item['assignee'] ?? $item['username'] ?? null;
        $email = $item['email'] ?? null;
        $avatar = null;

        if ($matchedUser) {
            $userId = $matchedUser->id;
            $name = $matchedUser->full_name ?? $matchedUser->name ?? $name;
            $jobTitle = $matchedUser->job_title ?? $jobTitle;
            $username = $matchedUser->username ?? $matchedUser->email ?? $username;
            $email = $matchedUser->email ?? $email;
            $avatar = $matchedUser->avatar;
            if ($activeTasks === null) {
                $activeTasks = Task::where('assignee_id', $matchedUser->id)
                    ->whereNotIn('status', ['done', 'closed', 'completed'])
                    ->count();
            }
        }

        if ($userId === null || $userId === '' || ($name === null || $name === '')) {
            return null;
        }

        return [
            'user_id' => $userId,
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'avatar' => $avatar,
            'job_title' => $jobTitle,
            'score' => $score,
            'confidence_percent' => $confidence ?? (int) round($score * 100),
            'rank_position' => $rank,
            'match_reasons' => $matchReasons,
            'active_tasks_count' => $activeTasks ?? 0,
        ];
    }

    /**
     * Fetch candidate users in the project or system.
     */
    protected function getCandidateUsers(?int $projectId = null)
    {
        $query = User::with('skills')->where('status', 'active');

        if ($projectId) {
            $project = Project::with('users')->find($projectId);
            if ($project && $project->users->count() > 0) {
                $members = $project->users()->with('skills')->where('status', 'active')->get();
                $withUsername = $members->filter(function (User $user) {
                    return filled($user->username);
                })->values();

                return $withUsername->isNotEmpty() ? $withUsername : $members;
            }
        }

        return $query->limit(20)->get();
    }

    protected function historyTasksForProject(int $projectId)
    {
        if (!isset($this->projectHistoryCache[$projectId])) {
            $this->projectHistoryCache[$projectId] = Task::query()
                ->with(['assignee', 'skills'])
                ->where('project_id', $projectId)
                ->whereNotNull('assignee_id')
                ->where(function ($query) {
                    $query->whereNull('labels')
                        ->orWhere('labels', 'not like', '%ai-test%');
                })
                ->latest('id')
                ->limit(150)
                ->get();
        }

        return $this->projectHistoryCache[$projectId];
    }

    /**
     * Run Python script inference.
     */
    protected function runPythonInference(array $payload, int $topK): array
    {
        $pythonBinary = base_path('.venv/bin/python3');
        if (!file_exists($pythonBinary)) {
            $pythonBinary = 'python3';
        }

        $scriptPath = base_path('AI/assignee_recommender.py');
        if (!file_exists($scriptPath)) {
            return [];
        }

        $jsonPayload = json_encode($payload, JSON_UNESCAPED_UNICODE);
        $command = sprintf(
            '%s %s predict --issue-json %s --top-k %d',
            escapeshellarg($pythonBinary),
            escapeshellarg($scriptPath),
            escapeshellarg($jsonPayload),
            $topK
        );

        $output = shell_exec($command);

        if (!$output) {
            return [];
        }

        $decoded = json_decode($output, true);
        return is_array($decoded) ? $decoded : [];
    }
}
