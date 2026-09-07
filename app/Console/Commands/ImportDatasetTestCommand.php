<?php

namespace ProjectsManager\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\RuntimeException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use ProjectsManager\AiAssigneeRecommendation;
use ProjectsManager\Project;
use ProjectsManager\Role;
use ProjectsManager\Task;
use ProjectsManager\TaskSkill;
use ProjectsManager\User;
use ProjectsManager\UserSkill;
use ProjectsManager\Worklog;

class ImportDatasetTestCommand extends Command
{
    const MARKER = 'dataset-test';
    const EMAIL_DOMAIN = 'dataset.test';

    protected $signature = 'dataset:import-test
        {--limit=300 : Number of holdout test issues to import}
        {--projects=8 : Max distinct projects from the test split}
        {--unassigned=40 : Percent of imported issues left unassigned for AI testing}
        {--members=12 : Extra candidate members per project}
        {--history-per-member=1 : Test-pool issues kept assigned only if a person has no other history}
        {--history-cap=8 : Max test-pool issues converted to history per project}
        {--fresh : Delete previously imported dataset records first}
        {--skip-extract : Reuse storage/app/dataset_test_subset.json}';

    protected $description = 'Import a sample of the AI holdout test dataset into the database';

    /**
     * Import a bounded holdout dataset.
     *
     * @return int
     */
    public function handle(): int
    {
        @ini_set('memory_limit', '512M');
        @set_time_limit(0);

        $jsonPath = storage_path('app/dataset_test_subset.json');
        if (!$this->option('skip-extract')) {
            if (!$this->extractSubset($jsonPath)) {
                return 1;
            }
        }

        if (!is_file($jsonPath)) {
            $this->error('Missing ' . $jsonPath . '. Run without --skip-extract first.');
            return 1;
        }

        $payload = json_decode(file_get_contents($jsonPath), true);
        if (!is_array($payload) || empty($payload['issues'])) {
            $this->error('dataset_test_subset.json is empty or invalid.');
            return 1;
        }

        if ($this->option('fresh')) {
            $this->purgeImported();
        }

        $admin = $this->resolveAdmin();
        $memberRoleId = Role::firstOrCreate(['id' => 3], ['name' => 'Member'])->id;
        $password = bcrypt('12345678');

        $userMap = [];
        $projectMap = [];

        DB::transaction(function () use ($payload, $admin, $memberRoleId, $password, &$userMap, &$projectMap) {
            $taskDispatcher = Task::getEventDispatcher();
            Task::unsetEventDispatcher();

            try {
                $userMap = $this->importUsers($payload['users'] ?? [], $memberRoleId, $password);
                $projectMap = $this->importProjects($payload['projects'] ?? [], $admin, $userMap);
                $this->attachPayloadMemberships($payload['users'] ?? [], $userMap, $projectMap);
                foreach (array_unique(array_values($projectMap)) as $projectId) {
                    $this->attachMember($projectId, $admin->id, 'owner');
                }
                $this->importIssues($payload['issues'] ?? [], $admin, $userMap, $projectMap);
            } finally {
                if ($taskDispatcher) {
                    Task::setEventDispatcher($taskDispatcher);
                }
            }
        });

        $unassigned = Task::query()
            ->whereHas('project', function ($query) {
                $query->where('customer_name', self::MARKER);
            })
            ->whereNull('assignee_id')
            ->whereNotIn('status', ['done', 'closed', 'completed', 'resolved'])
            ->count();

        $this->info('Imported dataset test sample.');
        $this->line('  Users     : ' . count($userMap) . '  (password: 12345678, email *@' . self::EMAIL_DOMAIN . ')');
        $this->line('  Projects  : ' . count(array_unique(array_values($projectMap))));
        $this->line('  Issues    : ' . count($payload['issues']));
        $this->line('  Unassigned open tasks (for AI): ' . $unassigned);
        $this->line('Tài khoản test (mật khẩu 12345678):');
        $this->line('  Admin: ' . $admin->email);
        foreach (['jnp', 'u39kun', 'mahadev', 'venkatnrangan'] as $username) {
            $account = User::where('username', $username)->first();
            if ($account) {
                $this->line('  ' . $account->name . ' (' . $username . '): ' . $account->email);
            }
        }
        $this->line('Login as ' . $admin->email . ' then mở /projects?search=Ambari hoặc /tasks.');

        return 0;
    }

    protected function extractSubset(string $jsonPath): bool
    {
        $dataset = base_path('AI/dataset/issues_clean.csv');
        if (!is_file($dataset)) {
            $this->error('Không tìm thấy AI/dataset/issues_clean.csv');
            return false;
        }

        $python = base_path('.venv/bin/python3');
        if (!is_file($python)) {
            $python = 'python3';
        }

        $script = base_path('AI/export_test_subset.py');
        $process = new Process([
            $python, $script, '--output', $jsonPath,
            '--limit', (string) (int) $this->option('limit'),
            '--projects', (string) (int) $this->option('projects'),
            '--unassigned', (string) (int) $this->option('unassigned'),
            '--members', (string) (int) $this->option('members'),
            '--history-per-project', '24',
        ], base_path());
        $process->setTimeout(300);
        $this->info('Extracting holdout test subset from CSV...');
        try {
            $process->mustRun(function ($type, $buffer) {
                $this->output->write($buffer);
            });
        } catch (RuntimeException $e) {
            $this->error('Python extractor failed: ' . $e->getMessage());
            return false;
        }

        return true;
    }

    protected function purgeImported(): void
    {
        $this->warn('Removing previously imported dataset records...');

        $projectIds = Project::query()->where('customer_name', self::MARKER)->pluck('id');
        $taskIds = $projectIds->isEmpty()
            ? collect()
            : Task::query()->whereIn('project_id', $projectIds)->pluck('id');

        if ($taskIds->isNotEmpty()) {
            if (Schema::hasTable('ai_assignee_recommendations')) {
                AiAssigneeRecommendation::query()->whereIn('task_id', $taskIds)->delete();
            }
            if (Schema::hasTable('task_skills')) {
                TaskSkill::query()->whereIn('task_id', $taskIds)->delete();
            }
            if (Schema::hasTable('worklogs')) {
                Worklog::query()->whereIn('task_id', $taskIds)->delete();
            }
            if (Schema::hasTable('task_user')) {
                DB::table('task_user')->whereIn('task_id', $taskIds)->delete();
            }
            Task::query()->whereIn('id', $taskIds)->delete();
        }

        if ($projectIds->isNotEmpty()) {
            DB::table('project_user')->whereIn('project_id', $projectIds)->delete();
            Project::query()->whereIn('id', $projectIds)->delete();
        }

        $userIds = User::query()->where('email', 'like', '%@' . self::EMAIL_DOMAIN)->pluck('id');
        if ($userIds->isNotEmpty()) {
            if (Schema::hasTable('user_skills')) {
                UserSkill::query()->whereIn('user_id', $userIds)->delete();
            }
            DB::table('project_user')->whereIn('user_id', $userIds)->delete();
            User::query()->whereIn('id', $userIds)->delete();
        }
    }

    protected function resolveAdmin(): User
    {
        $admin = User::where('email', 'trannguyentuan1807@gmail.com')->first()
            ?: User::where('role_id', 1)->orderBy('id')->first()
            ?: User::orderBy('id')->first();

        if (!$admin) {
            Role::firstOrCreate(['id' => 1], ['name' => 'Admin']);
            $admin = User::create([
                'name' => 'Tuấn Trần Nguyễn',
                'email' => 'trannguyentuan1807@gmail.com',
                'password' => bcrypt('12345678'),
                'role_id' => 1,
                'status' => 'active',
            ]);
        }

        return $admin;
    }

    protected function importUsers(array $rows, int $memberRoleId, string $password): array
    {
        $map = [];
        foreach ($rows as $index => $row) {
            $username = $this->clip($row['username'] ?? '', 80);
            if ($username === '') {
                continue;
            }

            $email = $this->emailFor($username, $index);
            $user = User::where('username', $username)->first()
                ?: User::where('email', $email)->first();

            if (!$user) {
                $user = new User();
                $user->email = $email;
                $user->password = $password;
                $user->role_id = $memberRoleId;
            }

            $display = $this->clip($row['display_name'] ?? $username, 120) ?: $username;
            $user->name = $display;
            $user->username = $username;
            $user->full_name = $display;
            $user->status = !empty($row['active']) ? 'active' : 'inactive';
            $user->job_title = $this->clip($row['skills'][0] ?? 'Contributor', 120);
            $user->department = $this->clip($row['projects'][0] ?? 'Dataset', 80);
            $user->save();

            if (!empty($row['skills']) && Schema::hasTable('user_skills')) {
                foreach (array_slice($row['skills'], 0, 6) as $skill) {
                    $skill = $this->clip($skill, 80);
                    if ($skill === '') {
                        continue;
                    }
                    UserSkill::firstOrCreate(
                        ['user_id' => $user->id, 'skill_name' => $skill],
                        ['proficiency_level' => 3]
                    );
                }
            }

            $map[strtolower($username)] = $user->id;
        }

        return $map;
    }

    protected function importProjects(array $rows, User $admin, array $userMap): array
    {
        $map = [];
        foreach ($rows as $row) {
            $code = $this->clip($row['key'] ?? '', 30);
            if ($code === '') {
                continue;
            }

            $project = Project::where('project_code', $code)
                ->where('customer_name', self::MARKER)
                ->first();

            if (!$project) {
                $project = new Project();
                $project->user_id = $admin->id;
                $project->project_code = $code;
                $project->customer_name = self::MARKER;
            }

            $project->name = $this->clip($row['name'] ?? $code, 180) ?: $code;
            $project->description = $this->clip($row['description'] ?? '', 5000);
            $project->status = 'active';
            $project->archived = false;
            $project->project_type = 'team';
            $project->priority = 'medium';
            $project->team_name = $this->clip($row['category'] ?? $code, 80);
            $project->save();

            $lead = strtolower($row['lead'] ?? '');
            if ($lead !== '' && isset($userMap[$lead])) {
                $this->attachMember($project->id, $userMap[$lead], 'manager');
            }

            $map[strtolower($code)] = $project->id;
            if (!empty($row['name'])) {
                $map[strtolower($row['name'])] = $project->id;
            }
        }

        return $map;
    }

    protected function selectHistorySeeds(array $issues, array $userMap, array $projectMap): array
    {
        $historyPerMember = max(1, (int) $this->option('history-per-member'));
        $historyCap = max($historyPerMember, (int) $this->option('history-cap'));
        $existing = [];
        $flagged = [];

        foreach ($issues as $index => $row) {
            $projectId = $projectMap[strtolower($row['projectname'] ?? '')] ?? null;
            $assigneeId = $userMap[strtolower($row['assignee'] ?? '')] ?? null;
            if (!$projectId || !$assigneeId) {
                continue;
            }
            if (empty($row['leave_unassigned'])) {
                $existing[$projectId][$assigneeId] = ($existing[$projectId][$assigneeId] ?? 0) + 1;
                continue;
            }
            $flagged[$projectId][$assigneeId][] = $index;
        }

        $seeds = [];
        foreach ($flagged as $projectId => $byAssignee) {
            $converted = 0;
            foreach ($byAssignee as $assigneeId => $indices) {
                $have = $existing[$projectId][$assigneeId] ?? 0;
                if ($have >= 1) {
                    continue;
                }
                $need = min($historyPerMember, max(0, $historyCap - $converted));
                if ($need <= 0) {
                    continue;
                }
                usort($indices, function ($left, $right) use ($issues) {
                    $leftCount = count($issues[$left]['components'] ?? []);
                    $rightCount = count($issues[$right]['components'] ?? []);
                    return $rightCount <=> $leftCount;
                });
                foreach (array_slice($indices, 0, $need) as $index) {
                    $seeds[$index] = true;
                    $converted++;
                }
            }
        }

        return $seeds;
    }

    protected function importIssues(array $issues, User $admin, array $userMap, array $projectMap): void
    {
        $historySeeds = $this->selectHistorySeeds($issues, $userMap, $projectMap);

        foreach ($issues as $index => $row) {
            $projectKey = strtolower($row['projectname'] ?? '');
            $projectId = $projectMap[$projectKey] ?? null;
            if (!$projectId) {
                continue;
            }

            $assigneeKey = strtolower($row['assignee'] ?? '');
            $reporterKey = strtolower($row['reporter'] ?? '');
            $assigneeId = $userMap[$assigneeKey] ?? null;
            $reporterId = $userMap[$reporterKey] ?? $admin->id;

            if ($assigneeId) {
                $this->attachMember($projectId, $assigneeId, 'member');
            }
            if ($reporterId) {
                $this->attachMember($projectId, $reporterId, 'member');
            }

            $issueKey = $this->clip($row['key'] ?? '', 30);
            $task = $issueKey !== '' ? Task::where('issue_key', $issueKey)->first() : null;
            if (!$task) {
                $task = new Task();
            }

            $unassigned = !empty($row['leave_unassigned']) && empty($historySeeds[$index]);
            $status = $this->mapStatus($row['status'] ?? '');
            if ($unassigned && in_array($status, ['done', 'closed', 'completed', 'resolved'], true)) {
                $status = 'todo';
            }

            $task->name = $this->clip($row['summary'] ?? 'Untitled issue', 180);
            $task->description = $this->clip($row['summary'] ?? '', 8000);
            $task->project_id = $projectId;
            $task->user_id = $reporterId ?: $admin->id;
            $task->reporter_id = $reporterId ?: $admin->id;
            $task->assignee_id = $unassigned ? null : $assigneeId;
            $task->issue_type = $this->mapIssueType($row['issuetype'] ?? 'task');
            $task->priority = $this->mapPriority($row['priority'] ?? 'medium');
            $task->status = $status;
            $labelParts = is_array($row['labels'] ?? null) ? $row['labels'] : [];
            if ($unassigned) {
                array_unshift($labelParts, 'ai-test');
            }
            $task->labels = $this->clip(implode(', ', array_filter($labelParts)), 1000);
            $task->due_date = $this->parseDate($row['duedate'] ?? null);
            $task->start_date = $this->parseDate($row['created'] ?? null);
            if ($issueKey !== '') {
                $task->issue_key = $issueKey;
            }
            $task->save();

            if ($issueKey === '') {
                $task->issue_key = 'DS-' . str_pad((string) $task->id, 5, '0', STR_PAD_LEFT);
                $task->save();
            }

            if (!empty($row['components']) && Schema::hasTable('task_skills')) {
                foreach (array_slice($row['components'], 0, 5) as $skill) {
                    $skill = $this->clip($skill, 80);
                    if ($skill === '') {
                        continue;
                    }
                    TaskSkill::firstOrCreate(['task_id' => $task->id, 'skill_name' => $skill]);
                }
            }
        }
    }

    protected function attachPayloadMemberships(array $users, array $userMap, array $projectMap): void
    {
        foreach ($users as $row) {
            $userId = $userMap[strtolower($row['username'] ?? '')] ?? null;
            if (!$userId) {
                continue;
            }
            foreach ($row['projects'] ?? [] as $code) {
                $projectId = $projectMap[strtolower($code)] ?? null;
                if ($projectId) {
                    $this->attachMember($projectId, $userId, 'member');
                }
            }
        }
    }

    protected function attachMember(int $projectId, int $userId, string $role = 'member'): void
    {
        if (!$projectId || !$userId) {
            return;
        }

        $exists = DB::table('project_user')
            ->where('project_id', $projectId)
            ->where('user_id', $userId)
            ->exists();

        if ($exists) {
            return;
        }

        $payload = [
            'project_id' => $projectId,
            'user_id' => $userId,
            'created_at' => now(),
            'updated_at' => now(),
        ];
        if (Schema::hasColumn('project_user', 'role')) {
            $payload['role'] = $role;
        }

        DB::table('project_user')->insert($payload);
    }

    protected function mapIssueType($value): string
    {
        $value = strtolower(trim((string) $value));
        if (strpos($value, 'bug') !== false) {
            return 'bug';
        }
        if (strpos($value, 'epic') !== false) {
            return 'epic';
        }
        if (strpos($value, 'sub') !== false) {
            return 'subtask';
        }
        if (strpos($value, 'story') !== false || strpos($value, 'feature') !== false || strpos($value, 'improvement') !== false) {
            return 'story';
        }
        return 'task';
    }

    protected function mapPriority($value): string
    {
        $value = strtolower(trim((string) $value));
        if (in_array($value, ['blocker', 'critical', 'urgent', 'highest'], true)) {
            return 'urgent';
        }
        if (in_array($value, ['major', 'high'], true)) {
            return 'high';
        }
        if (in_array($value, ['minor', 'low', 'trivial', 'lowest'], true)) {
            return 'low';
        }
        return 'medium';
    }

    protected function mapStatus($value): string
    {
        $value = strtolower(trim((string) $value));
        if (in_array($value, ['closed', 'resolved', 'done', 'completed', 'fixed', 'cancelled', 'canceled'], true)) {
            return 'done';
        }
        if (strpos($value, 'progress') !== false || in_array($value, ['reopened', 'review', 'in review', 'patch available'], true)) {
            return 'in_progress';
        }
        return 'todo';
    }

    protected function parseDate($value): ?string
    {
        $value = trim((string) $value);
        if ($value === '' || strtolower($value) === 'nan') {
            return null;
        }
        $timestamp = strtotime($value);
        if ($timestamp === false) {
            return null;
        }
        return date('Y-m-d', $timestamp);
    }

    protected function emailFor(string $username, int $index): string
    {
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '.', $username));
        $slug = trim($slug, '.');
        if ($slug === '') {
            $slug = 'user' . $index;
        }
        $slug = substr($slug, 0, 40);
        return $slug . '.' . $index . '@' . self::EMAIL_DOMAIN;
    }

    protected function clip($value, int $max): string
    {
        $value = trim((string) $value);
        if ($value === '' || strtolower($value) === 'nan') {
            return '';
        }
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $max);
        }
        return substr($value, 0, $max);
    }
}
