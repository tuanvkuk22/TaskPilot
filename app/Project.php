<?php

namespace ProjectsManager;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    //
    protected $fillable = [
        'name',
        'image',
        'description',
        'company_id',
        'user_id',
        'days', 'status', 'archived', 'project_type', 'workflow_config', 'team_name', 'project_code',
        'start_date', 'end_date', 'budget', 'priority', 'customer_name', 'location',
        'objective', 'category_id', 'team_id',
    ];

    protected $casts = [
        'archived' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'budget' => 'decimal:2',
        'workflow_config' => 'array',
    ];

    public static function defaultWorkflowForType(?string $type = 'software'): array
    {
        $type = strtolower((string) $type);
        if ($type === 'marketing') {
            return [
                'project_type' => 'marketing',
                'statuses' => [
                    ['key' => 'todo', 'name' => 'Lên ý tưởng / Chuẩn bị', 'color' => '#64748b', 'category' => 'todo', 'is_initial' => true, 'icon' => 'fa-lightbulb-o'],
                    ['key' => 'in_progress', 'name' => 'Đang thực hiện', 'color' => '#3b82f6', 'category' => 'in_progress', 'icon' => 'fa-pencil'],
                    ['key' => 'done', 'name' => 'Hoàn thành', 'color' => '#10b981', 'category' => 'done', 'is_final' => true, 'icon' => 'fa-check-circle'],
                ],
            ];
        }

        if ($type === 'hr') {
            return [
                'project_type' => 'hr',
                'statuses' => [
                    ['key' => 'todo', 'name' => 'Tiếp nhận / Cần xử lý', 'color' => '#64748b', 'category' => 'todo', 'is_initial' => true, 'icon' => 'fa-file-text-o'],
                    ['key' => 'in_progress', 'name' => 'Đang xử lý', 'color' => '#3b82f6', 'category' => 'in_progress', 'icon' => 'fa-users'],
                    ['key' => 'done', 'name' => 'Hoàn tất', 'color' => '#10b981', 'category' => 'done', 'is_final' => true, 'icon' => 'fa-check-circle'],
                ],
            ];
        }

        // Default: Software
        return [
            'project_type' => 'software',
            'statuses' => [
                ['key' => 'todo', 'name' => 'Cần làm', 'color' => '#64748b', 'category' => 'todo', 'is_initial' => true, 'icon' => 'fa-circle-o'],
                ['key' => 'in_progress', 'name' => 'Đang thực hiện', 'color' => '#3b82f6', 'category' => 'in_progress', 'icon' => 'fa-spinner'],
                ['key' => 'done', 'name' => 'Hoàn thành', 'color' => '#10b981', 'category' => 'done', 'is_final' => true, 'icon' => 'fa-check-circle'],
            ],
        ];
    }

    public function getEffectiveWorkflowAttribute(): array
    {
        if (!empty($this->workflow_config) && is_array($this->workflow_config) && !empty($this->workflow_config['statuses'])) {
            return $this->workflow_config;
        }

        return self::defaultWorkflowForType($this->project_type ?? 'software');
    }

    public function getActiveStatuses(): array
    {
        $workflow = $this->effective_workflow;
        $statuses = $workflow['statuses'] ?? [];

        return array_values(array_filter($statuses, function ($status) {
            return ($status['is_active'] ?? true) !== false;
        }));
    }

    public function findWorkflowStatus(string $statusKey): ?array
    {
        $statuses = $this->effective_workflow['statuses'] ?? [];
        foreach ($statuses as $s) {
            if ($s['key'] === $statusKey) {
                return $s;
            }
        }
        return null;
    }

    public function canTransitionTaskStatus(Task $task, string $targetStatus, ?User $user = null): array
    {
        if (!$user && class_exists(\Illuminate\Support\Facades\Facade::class) && \Illuminate\Support\Facades\Facade::getFacadeApplication()) {
            $user = \Illuminate\Support\Facades\Auth::user();
        }
        if (!$user) {
            return ['allowed' => false, 'message' => 'Vui lòng đăng nhập để thực hiện.', 'status_code' => 401];
        }

        $workflow = $this->effective_workflow;
        $currentKey = $task->status;

        $targetStatusObj = $this->findWorkflowStatus($targetStatus);
        $currentStatusObj = $this->findWorkflowStatus($currentKey);

        $targetName = $targetStatusObj['name'] ?? $targetStatus;
        $currentName = $currentStatusObj['name'] ?? $currentKey;

        // 1. Target status exists in project workflow
        if (!$targetStatusObj) {
            return [
                'allowed' => false,
                'message' => "Trạng thái '{$targetName}' không tồn tại trong quy trình của dự án.",
                'status_code' => 422,
            ];
        }

        // 2. Target status is not disabled
        if (($targetStatusObj['is_active'] ?? true) === false && $currentKey !== $targetStatus) {
            return [
                'allowed' => false,
                'message' => "Trạng thái '{$targetName}' đã bị vô hiệu hóa trong quy trình.",
                'status_code' => 422,
            ];
        }

        // 3. Check transition rules if defined
        $transitions = $workflow['transitions'] ?? [];
        if (!empty($transitions) && isset($transitions[$currentKey])) {
            $allowedTargets = (array) $transitions[$currentKey];
            if (!empty($allowedTargets) && !in_array($targetStatus, $allowedTargets, true)) {
                return [
                    'allowed' => false,
                    'message' => "Quy trình dự án không cho phép chuyển trực tiếp từ '{$currentName}' sang '{$targetName}'.",
                    'status_code' => 422,
                ];
            }
        }

        // 4. Permissions check
        $isOwner = ((int) $this->user_id === (int) $user->id);
        $isAssignee = ((int) $task->assignee_id === (int) $user->id);

        if (!$isOwner) {
            $member = $this->users()->where('users.id', $user->id)->first();
            if (!$member && !$isAssignee) {
                return [
                    'allowed' => false,
                    'message' => 'Bạn không phải là thành viên của dự án này.',
                    'status_code' => 403,
                ];
            }

            // User permissions
            $userPerms = [];
            if ($member) {
                $raw = $member->pivot->permissions ?? '[]';
                $userPerms = is_string($raw) ? (json_decode($raw, true) ?? []) : (array) $raw;
                if (!empty($member->pivot->role)) {
                    $roleObj = $this->roles()->where('name', $member->pivot->role)->first();
                    if ($roleObj && is_array($roleObj->permissions)) {
                        $userPerms = array_merge($userPerms, $roleObj->permissions);
                    }
                }
            }

            // Check transition permissions if configured for target status
            $transitionPerms = $workflow['transition_permissions'] ?? [];
            if (!empty($transitionPerms[$targetStatus])) {
                $required = (array) $transitionPerms[$targetStatus];
                $hasRequired = false;
                foreach ($required as $r) {
                    if (in_array($r, $userPerms, true) || ($member && $member->pivot->role === $r)) {
                        $hasRequired = true;
                        break;
                    }
                }
                if (!$hasRequired) {
                    return [
                        'allowed' => false,
                        'message' => "Bạn không có quyền hoặc vai trò được yêu cầu để chuyển sang trạng thái '{$targetName}'.",
                        'status_code' => 403,
                    ];
                }
            }

            // General status permission check: must be assignee OR have task.status / task.edit / task.manage
            if (!$isAssignee && !empty($userPerms) && empty(array_intersect(['task.status', 'task.edit', 'task.manage'], $userPerms))) {
                return [
                    'allowed' => false,
                    'message' => 'Bạn không có quyền cập nhật trạng thái công việc trong dự án này.',
                    'status_code' => 403,
                ];
            }
        }

        return ['allowed' => true, 'message' => null];
    }



    public function users(){
		return $this->belongsToMany(User::class)->withPivot('role', 'permissions');
    }

    public function members(){
        return $this->users();
    }

    public function roles(){
        return $this->hasMany(ProjectRole::class);
    }

    public function company(){
		return $this->belongsTo(Company::class);
    }

    public function comments()
    {
        return $this->morphMany('ProjectsManager\Comment', 'commentable');
    }

    public function tasks() { return $this->hasMany(Task::class); }
    public function category() { return $this->belongsTo(ProjectCategory::class); }
    public function team() { return $this->belongsTo(Team::class); }
    public function sprints() { return $this->hasMany(Sprint::class); }
    public function permissions() { return $this->hasMany(ProjectPermission::class); }
    public function meetings() { return $this->hasMany(Meeting::class); }
    public function documents() { return $this->hasMany(ProjectDocument::class); }
    public function forms() { return $this->hasMany(ProjectForm::class); }
    public function developmentLinks() { return $this->hasMany(ProjectDevelopmentLink::class); }
    public function activities() { return $this->hasMany(ProjectActivity::class)->latest('created_at'); }

    public function scopeStatusFilter($query, $status)
    {
        if (empty($status) || $status === 'all') {
            return $query;
        }

        return $query->where('status', $status);
    }
}
