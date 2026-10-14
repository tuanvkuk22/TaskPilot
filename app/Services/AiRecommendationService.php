<?php

namespace ProjectsManager\Services;

use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use ProjectsManager\AiAssigneeRecommendation;
use ProjectsManager\Project;
use ProjectsManager\Task;
use ProjectsManager\User;
use Symfony\Component\Process\Process;
use UnexpectedValueException;

class AiRecommendationService
{
    private const HISTORY_LIMIT = 50;

    protected array $projectHistoryCache = [];

    /**
     * Recommend assignees for a given task context.
     *
     * @param array $taskData
     * @param int|null $projectId
     * @param int $topK
     *
     * @return array
     */
    public function recommend(array $taskData, ?int $projectId = null, int $topK = 5): array
    {
        $this->projectHistoryCache = [];
        $projectId = $projectId ?? $taskData['project_id'] ?? null;
        $candidateUsers = $this->getCandidateUsers($projectId);

        return $this->recommendWithCandidates($taskData, $candidateUsers, $projectId, $topK, true);
    }

    /**
     * Recommend assignees for many existing tasks, reusing candidates per project.
     *
     * @param iterable $tasks
     * @param int $topK
     *
     * @return array
     */
    public function recommendMany(iterable $tasks, int $topK = 3): array
    {
        $this->projectHistoryCache = [];
        $candidateCache = [];
        $contextCache = [];
        $jobs = [];

        foreach ($tasks as $task) {
            $projectId = $task->project_id ? (int) $task->project_id : null;
            $cacheKey = $projectId ?? 0;
            if (!isset($candidateCache[$cacheKey])) {
                $candidateCache[$cacheKey] = $this->getCandidateUsers($projectId);
            }
            $candidates = $candidateCache[$cacheKey];
            if ($candidates->isEmpty()) {
                $jobs[] = [
                    'task' => $task,
                    'project_id' => $projectId,
                    'candidates' => $candidates,
                    'payload' => null,
                    'recommendations' => [],
                ];
                continue;
            }
            if (!isset($contextCache[$cacheKey])) {
                $contextCache[$cacheKey] = $this->inferenceContext($candidates, $projectId);
            }
            $jobs[] = [
                'task' => $task,
                'project_id' => $projectId,
                'candidates' => $candidates,
                'payload' => $this->buildInferencePayload([
                    'name' => $task->name,
                    'summary' => $task->name,
                    'description' => $task->description,
                    'issue_type' => $task->issue_type ?? 'task',
                    'priority' => $task->priority ?? 'medium',
                    'labels' => $task->labels ?? '',
                    'task_id' => $task->id,
                    'project_id' => $projectId,
                    'components' => $task->skills ? $task->skills->pluck('skill_name')->toArray() : [],
                ], $contextCache[$cacheKey]),
                'recommendations' => null,
            ];
        }

        $pendingIndexes = [];
        foreach ($jobs as $index => $job) {
            if ($job['payload'] !== null) {
                $pendingIndexes[] = $index;
            }
        }
        if ($pendingIndexes) {
            $payloads = array_map(static fn (int $index) => $jobs[$index]['payload'], $pendingIndexes);
            $batch = $this->runPythonInferenceBatch(array_values($payloads), $topK);
            foreach (array_values($pendingIndexes) as $batchIndex => $jobIndex) {
                $jobs[$jobIndex]['recommendations'] = $this->mapRecommendations(
                    $batch[$batchIndex] ?? [],
                    $jobs[$jobIndex]['candidates'],
                    ['task_id' => $jobs[$jobIndex]['task']->id],
                    false
                );
            }
        }

        $items = [];
        foreach ($jobs as $job) {
            $task = $job['task'];
            $recommendations = $job['recommendations'] ?? [];
            $items[] = [
                'task_id' => $task->id,
                'task_name' => $task->name,
                'issue_type' => $task->issue_type,
                'priority' => $task->priority,
                'project_id' => $job['project_id'],
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
        Collection $candidateUsers,
        ?int $projectId,
        int $topK,
        bool $persist
    ): array {
        if ($candidateUsers->isEmpty()) {
            return [];
        }

        $payload = $this->buildInferencePayload($taskData, $this->inferenceContext($candidateUsers, $projectId));
        return $this->mapRecommendations($this->runPythonInference($payload, $topK), $candidateUsers, $taskData, $persist);
    }

    /**
     * Shared candidate list, project name, and assignment history for one project.
     */
    protected function inferenceContext(Collection $candidateUsers, ?int $projectId): array
    {
        $workloads = Task::query()->whereIn('assignee_id', $candidateUsers->modelKeys())
            ->whereNotIn('status', ['done', 'closed', 'completed', 'resolved'])
            ->selectRaw('assignee_id, COUNT(*) as active_count')->groupBy('assignee_id')
            ->pluck('active_count', 'assignee_id');
        $formattedCandidates = $candidateUsers->map(function (User $user) use ($workloads) {
            $skills = $user->skills ? $user->skills->pluck('skill_name')->toArray() : [];

            return [
                'id' => $user->id,
                'name' => $user->full_name ?? $user->name,
                'username' => $user->username ?? $user->email,
                'email' => $user->email,
                'job_title' => $user->job_title ?? '',
                'department' => $user->department ?? '',
                'status' => $user->status ?? 'active',
                'skills' => $skills,
                'active_tasks_count' => (int) ($workloads[$user->id] ?? 0),
            ];
        })->toArray();

        $projectName = '';
        $projectCode = '';
        $history = [];
        if ($projectId) {
            $project = Project::find($projectId);
            if ($project) {
                $projectName = $project->name;
                $projectCode = $project->project_code ?: $project->name;
            }
            foreach ($this->historyTasksForProject($projectId) as $historyTask) {
                $history[] = [
                    'id' => (int) $historyTask->id,
                    'summary' => $historyTask->name,
                    'assignee' => $historyTask->assignee->username ?? $historyTask->assignee->email ?? '',
                    'username' => $historyTask->assignee->username ?? '',
                    'issuetype' => $historyTask->issue_type ?? 'task',
                    'components' => $historyTask->skills ? $historyTask->skills->pluck('skill_name')->implode(' ') : '',
                ];
            }
        }

        return [
            'candidates' => $formattedCandidates,
            'projectname' => $projectCode ?: $projectName,
            'history' => $history,
        ];
    }

    protected function buildInferencePayload(array $taskData, array $context): array
    {
        $components = [];
        if (isset($taskData['components']) && is_array($taskData['components'])) {
            $components = $taskData['components'];
        } elseif (!empty($taskData['task_id'])) {
            $current = Task::with('skills')->find($taskData['task_id']);
            if ($current && $current->skills) {
                $components = $current->skills->pluck('skill_name')->toArray();
            }
        }

        $taskId = !empty($taskData['task_id']) ? (int) $taskData['task_id'] : null;
        $history = $context['history'];
        if ($taskId) {
            $history = array_values(array_filter(
                $history,
                static fn (array $row) => (int) ($row['id'] ?? 0) !== $taskId
            ));
        }
        foreach ($history as &$row) {
            unset($row['id']);
        }
        unset($row);

        return [
            'summary' => $taskData['name'] ?? $taskData['summary'] ?? '',
            'description' => $taskData['description'] ?? '',
            'issuetype' => $taskData['issue_type'] ?? 'task',
            'priority' => $taskData['priority'] ?? 'medium',
            'projectname' => $context['projectname'],
            'components' => implode(' ', $components),
            'labels' => $taskData['labels'] ?? '',
            'reporter' => auth()->user()?->username ?? auth()->user()?->email ?? '',
            'candidate_users' => $context['candidates'],
            'history' => $history,
        ];
    }

    protected function mapRecommendations(array $recommendations, Collection $candidateUsers, array $taskData, bool $persist): array
    {
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
                    Log::warning('Failed to store AI recommendation log.', ['task_id' => $taskData['task_id']]);
                    $formatted['persistence_warning'] = 'Không thể lưu lịch sử gợi ý AI.';
                }
            }

            $results[] = $formatted;
        }

        return $results;
    }

    /**
     * Match a Python recommendation row back to a TaskPilot user.
     */
    protected function matchCandidateUser(Collection $candidateUsers, array $item): ?User
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
    protected function formatRecommendationForUi(array $item, ?User $matchedUser, int $rank): ?array
    {
        if (!$matchedUser) {
            return null;
        }
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
    protected function getCandidateUsers(?int $projectId = null): Collection
    {
        $query = User::with('skills')->where('status', 'active');

        if ($projectId) {
            $project = Project::find($projectId);
            if ($project) {
                $members = $project->users()->with('skills')->where('status', 'active')->get();

                return $members;
            }
        }

        return $query->limit(20)->get();
    }

    protected function historyTasksForProject(int $projectId): Collection
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
                ->limit(self::HISTORY_LIMIT)
                ->get();
        }

        return $this->projectHistoryCache[$projectId];
    }

    /**
     * Run Python script inference.
     */
    protected function runPythonInference(array $payload, int $topK): array
    {
        return $this->runPythonProcess($payload, $topK, 20);
    }

    /**
     * Rank many issues in one Python process.
     *
     * @param list<array> $payloads
     *
     * @return list<array>
     */
    protected function runPythonInferenceBatch(array $payloads, int $topK): array
    {
        if ($payloads === []) {
            return [];
        }

        return $this->runPythonProcess(['items' => array_values($payloads), 'top_k' => $topK], $topK, 60);
    }

    protected function runPythonProcess(array $payload, int $topK, int $timeout): array
    {
        $pythonBinary = base_path('.venv/bin/python3');
        if (!file_exists($pythonBinary)) {
            $pythonBinary = 'python3';
        }

        $scriptPath = base_path('AI/assignee_recommender.py');
        if (!file_exists($scriptPath)) {
            abort(503, 'Dịch vụ gợi ý AI chưa sẵn sàng.');
        }

        $process = new Process(
            [$pythonBinary, '-B', $scriptPath, 'predict', '--issue-json', '-', '--top-k', (string) $topK],
            base_path()
        );
        $process->setInput(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        $process->setTimeout($timeout);

        try {
            $process->mustRun();
            $decoded = json_decode($process->getOutput(), true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($decoded) || !array_is_list($decoded)) {
                throw new UnexpectedValueException('Invalid inference response.');
            }

            return $decoded;
        } catch (Exception $e) {
            Log::warning('AI inference failed.', ['exception' => get_class($e)]);
            abort(503, 'Không thể chạy gợi ý AI lúc này. Vui lòng thử lại.');
        }
    }
}
