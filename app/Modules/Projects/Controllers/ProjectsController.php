<?php

namespace ProjectsManager\Modules\Projects\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use ProjectsManager\Http\Controllers\Controller;
use ProjectsManager\Modules\Projects\Handlers\AddUserToProjectHandler;
use ProjectsManager\Modules\Projects\Handlers\ArchiveProjectHandler;
use ProjectsManager\Modules\Projects\Handlers\AssignMemberRoleHandler;
use ProjectsManager\Modules\Projects\Handlers\CreateProjectRoleHandler;
use ProjectsManager\Modules\Projects\Handlers\DeleteProjectHandler;
use ProjectsManager\Modules\Projects\Handlers\DeleteProjectRoleHandler;
use ProjectsManager\Modules\Projects\Handlers\GetProjectFormDataHandler;
use ProjectsManager\Modules\Projects\Handlers\ListProjectRolesHandler;
use ProjectsManager\Modules\Projects\Handlers\ListProjectsHandler;
use ProjectsManager\Modules\Projects\Handlers\ShowProjectHandler;
use ProjectsManager\Modules\Projects\Handlers\StoreProjectHandler;
use ProjectsManager\Modules\Projects\Handlers\StoreQuickCategoryHandler;
use ProjectsManager\Modules\Projects\Handlers\UpdateProjectHandler;
use ProjectsManager\Modules\Projects\Handlers\UpdateProjectRoleHandler;
use ProjectsManager\Modules\Projects\Repositories\ProjectRepository;
use ProjectsManager\Modules\Companies\Handlers\StoreCompanyHandler;
use ProjectsManager\Company;
use ProjectsManager\Project;
use ProjectsManager\Services\ActivityLogger;
use ProjectsManager\ProjectPermission;
use ProjectsManager\ProjectRole;
use ProjectsManager\ProjectRoleConstants;

class ProjectsController extends Controller
{
    public function __construct(
        private readonly ListProjectsHandler $listProjectsHandler,
        private readonly GetProjectFormDataHandler $formDataHandler,
        private readonly StoreProjectHandler $storeProjectHandler,
        private readonly ShowProjectHandler $showProjectHandler,
        private readonly UpdateProjectHandler $updateProjectHandler,
        private readonly ArchiveProjectHandler $archiveProjectHandler,
        private readonly DeleteProjectHandler $deleteProjectHandler,
        private readonly AddUserToProjectHandler $addUserHandler,
        private readonly StoreQuickCategoryHandler $quickCategoryHandler,
        private readonly StoreCompanyHandler $storeCompanyHandler,
        private readonly ListProjectRolesHandler $listRolesHandler,
        private readonly CreateProjectRoleHandler $createRoleHandler,
        private readonly UpdateProjectRoleHandler $updateRoleHandler,
        private readonly DeleteProjectRoleHandler $deleteRoleHandler,
        private readonly AssignMemberRoleHandler $assignMemberRoleHandler,
        private readonly ProjectRepository $projectRepository,
    ) {
    }

    public function index(Request $request): View|RedirectResponse
    {
        if (! Auth::check()) {
            return view('auth.login');
        }

        $status = $request->query('status');
        $userId = Auth::id();
        $projects = $this->listProjectsHandler->handle($userId, $status);

        $projects->each(function ($project) use ($userId) {
            $pivotRole = $project->users()
                ->where('users.id', $userId)
                ->value('role');

            $project->user_role = $pivotRole ?: ($project->user_id === $userId ? 'owner' : null);
        });

        $activeCount = $this->projectRepository->countForUser($userId, 'active');
        $archivedCount = $this->projectRepository->countForUser($userId, 'archived');
        $completedCount = $this->projectRepository->countForUser($userId, 'completed');

        return view('projects.index', [
            'projects' => $projects,
            'status' => $status,
            'stats' => [
                'total' => $projects->count(),
                'active' => $activeCount,
                'completed' => $completedCount,
                'archived' => $archivedCount,
            ],
        ]);
    }

    public function board(Project $project): View
    {
        $project->load(['tasks.assignee', 'tasks.reporter', 'tasks.project', 'tasks.parent']);

        $tasks = $project->tasks;
        $stats = [
            'total' => $tasks->count(),
            'in_progress' => $tasks->whereIn('status', ['in_progress', 'doing', 'review'])->count(),
            'done' => $tasks->whereIn('status', ['done', 'completed', 'resolved'])->count(),
            'high_priority' => $tasks->where('priority', 'high')->count(),
        ];

        return view('projects.board', [
            'project' => $project,
            'tasks' => $tasks,
            'stats' => $stats,
        ]);
    }

    public function timeline(Project $project): View
    {
        $project->load([
            'users',
            'tasks.assignee',
            'tasks.reporter',
            'tasks.parent',
            'tasks.children.assignee',
            'tasks.children.reporter',
            'sprints'
        ]);

        return view('projects.timeline', [
            'project' => $project,
            'tasks' => $project->tasks,
            'sprints' => $project->sprints ?? [],
        ]);
    }

    public function roadmap(Project $project): View
    {
        $project->load(['tasks.assignee']);

        return view('projects.roadmap', ['project' => $project]);
    }

    public function create(mixed $companyId = null): View
    {
        $formData = $this->formDataHandler->handle(Auth::id(), $companyId);

        return view('projects.create', $formData);
    }

    public function storeQuickCategory(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category = $this->quickCategoryHandler->handle(Auth::id(), (string) $request->input('name'));

        return response()->json([
            'id' => $category->id,
            'name' => $category->name,
        ]);
    }

    public function storeQuickCompany(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_address' => ['nullable', 'string', 'max:500'],
            'contact_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $company = $this->storeCompanyHandler->handle(Auth::id(), $data);

        return response()->json([
            'id' => $company->id,
            'name' => $company->name,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return back()->withInput()->withErrors(['project' => 'Không thể tạo dự án mới.']);
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'objective' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'category_id' => ['nullable', 'integer', 'exists:project_categories,id'],
            'category_name' => ['nullable', 'string', 'max:255'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'status' => ['required', 'string', 'in:active,planning,on_hold'],
            'priority' => ['required', 'string', 'in:low,medium,high,critical'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'budget' => ['nullable', 'numeric', 'min:0'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'members' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('project-images', 'public');
        }

        $result = $this->storeProjectHandler->handle(Auth::id(), $data);

        if (! $result['success']) {
            return back()->withInput()->withErrors(['project' => $result['error']]);
        }

        $redirect = redirect()->route('projects.show', ['project' => $result['project']->id]);

        if (isset($result['warning'])) {
            return $redirect->withErrors(['project' => $result['warning']]);
        }

        return $redirect->with('success', 'Dự án đã được tạo thành công.');
    }

    public function show(Project $project): View
    {
        $project = $this->showProjectHandler->handle($project);
        $project->load(['tasks.assignee', 'tasks.user']);
        $project->tasks->each(function ($task) {
            if (! $task->assignee && $task->user) {
                $task->assignee = $task->user;
            }
        });

        return view('projects.show', [
            'project' => $project,
            'comments' => $project->comments,
            'activities' => $project->activities,
        ]);
    }

    public function edit(Project $project): View
    {
        $formData = $this->formDataHandler->handle(Auth::id());

        return view('projects.edit', array_merge($formData, [
            'project' => $project,
            'errors' => session('errors') ? session('errors')->toArray() : [],
            'old' => old(),
            'update_url' => route('projects.update', $project->id),
        ]));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $data = $request->all();

        if ($request->has('company_id')) {
            $validated = $request->validate([
                'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            ]);

            $companyId = $validated['company_id'] ?? null;
            if ($companyId && ! Company::query()->whereKey($companyId)->where('user_id', Auth::id())->exists()) {
                return back()->withInput()->withErrors([
                    'company_id' => 'Khách hàng đã chọn không thuộc tài khoản của bạn.',
                ]);
            }

            // Keep this key even when blank so users can change or remove a customer.
            $data['company_id'] = $companyId;
        }

        if ($request->hasFile('image')) {
            $request->validate([
                'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            ]);

            if ($project->image) {
                Storage::disk('public')->delete($project->image);
            }

            $data['image'] = $request->file('image')->store('project-images', 'public');
        }

        if ($request->input('remove_image')) {
            if ($project->image) {
                Storage::disk('public')->delete($project->image);
            }
            $data['image'] = null;
        }

        $updated = $this->updateProjectHandler->handle($project, $data);

        if ($updated) {
            return redirect()->route('projects.show', ['project' => $project->id])
                ->with('success', 'Project updated successfully.');
        }

        return back()->withInput();
    }

    public function archive(Project $project): RedirectResponse
    {
        $this->archiveProjectHandler->handle($project);

        return redirect()->route('projects.index')
            ->with('success', 'Project archived successfully.');
    }

    public function destroy(Project $project): RedirectResponse|JsonResponse
    {
        $deleted = $this->deleteProjectHandler->handle($project);

        if ($deleted) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã xóa dự án thành công.',
                ]);
            }

            return redirect()->route('projects.index')
                ->with('success', 'Đã xóa dự án và các công việc liên quan.');
        }

        if (request()->expectsJson()) {
            return response()->json([
                'success' => false,
                'error' => 'Không thể xóa dự án.',
            ], 422);
        }

        return back()->withInput()->with('error', 'Không thể xóa dự án. Vui lòng thử lại.');
    }

    public function adduser(Request $request): RedirectResponse
    {
        $result = $this->addUserHandler->handle(
            Auth::id(),
            (int) $request->input('project_id'),
            $request->input('email'),
            (string) $request->input('role', 'member'),
            (array) $request->input('permissions', ['view', 'edit'])
        );

        if (! $result['success']) {
            if (isset($result['project_id'])) {
                return redirect()->route('projects.show', ['project' => $result['project_id']])
                    ->withErrors(['member' => $result['error']]);
            }

            return back()->withErrors(['member' => $result['error']]);
        }

        ActivityLogger::record(
            projectId: (int) $request->input('project_id'),
            action: ActivityLogger::ACTION_INVITED,
            userId: Auth::id(),
            subjectLabel: $request->input('email'),
            meta: (string) $request->input('role', 'member'),
        );

        return back()->with('success', $result['message']);
    }

    public function roles(Project $project): View
    {
        $this->ensureProjectOwner($project);

        $roles = $this->listRolesHandler->handle($project->id);

        return view('projects.roles', [
            'project' => $project,
            'roles' => $roles,
        ]);
    }

    public function availablePermissions(): JsonResponse
    {
        return response()->json([
            'permissions' => ProjectRoleConstants::PERMISSIONS,
            'groups' => ProjectRoleConstants::PERMISSION_GROUPS,
            'labels' => ProjectRoleConstants::PERMISSION_LABELS,
        ]);
    }

    // public function storeRole(Request $request, Project $project): RedirectResponse
    // {
    //     $this->ensureProjectOwner($project);

    //     $data = $request->validate([
    //         'name' => ['required', 'string', 'max:100'],
    //         'permissions' => ['nullable', 'array'],
    //         'permissions.*' => ['string', 'max:100'],
    //     ], [
    //         'name.required' => 'Vui lòng nhập tên vai trò.',
    //     ]);

    //     $name = trim($data['name']);

    //     if ($project->roles()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->exists()) {
    //         return back()->withErrors(['role_name' => 'Vai trò này đã tồn tại trong dự án.'])->withInput();
    //     }

    //     $this->createRoleHandler->handle($project->id, $name, $data['permissions'] ?? []);

    //     return back()->with('success', 'Đã thêm vai trò mới.');
    // }

    public function storeRole(Request $request, Project $project): JsonResponse
    {
        $this->ensureProjectOwner($project);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'max:100'],
        ], [
            'name.required' => 'Vui lòng nhập tên vai trò.',
        ]);

        $name = trim($data['name']);

        if ($project->roles()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->exists()) {
            return response()->json([
                'message' => 'Vai trò đã tồn tại.',
                'errors' => [
                    'role_name' => ['Vai trò này đã tồn tại trong dự án.'],
                ],
            ], 422);
        }

        $role = $this->createRoleHandler->handle(
            $project->id,
            $name,
            $data['permissions'] ?? []
        );

        return response()->json([
            'message' => 'Đã thêm vai trò mới.',
            'role' => $role,
        ], 201);
    }

    public function updateRole(Request $request, Project $project, $role): JsonResponse
    {
        $this->ensureProjectOwner($project);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', 'max:100'],
        ], [
            'name.required' => 'Vui lòng nhập tên vai trò.',
        ]);

        $role = $project->roles()->findOrFail($role);

        $name = trim($data['name']);

        if (
            $project->roles()
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->where('id', '!=', $role->id)
                ->exists()
        ) {
            return response()->json([
                'message' => 'Vai trò đã tồn tại.',
                'errors' => [
                    'name' => ['Vai trò này đã tồn tại trong dự án.'],
                ],
            ], 422);
        }

        $role->update([
            'name' => $name,
            'permissions' => $data['permissions'] ?? [],
        ]);

        return response()->json([
            'message' => 'Đã cập nhật vai trò.',
            'role' => $role,
        ]);
        }

    // public function destroyRole(Project $project, ProjectRole $role): RedirectResponse
    // {
    //     $this->ensureProjectOwner($project);
    //     $this->ensureRoleBelongsToProject($project, $role);

    //     $this->deleteRoleHandler->handle($role);

    //     return back()->with('success', 'Đã xóa vai trò.');
    // }
        public function destroyRole(Project $project, $role): JsonResponse
    {
        $this->ensureProjectOwner($project);

        $role = $project->roles()->findOrFail($role);

        $role->delete();

        return response()->json([
            'message' => 'Đã xóa vai trò.',
        ]);
    }
    public function members(Project $project): View
    {
        $this->ensureProjectOwner($project);

        $project->load('users', 'roles');

        $project->users->each(function ($user) {
            $name = trim($user->name ?: $user->email ?: '?');
            $parts = explode(' ', $name);

            if (count($parts) >= 2) {
                $user->avatar_fallback = strtoupper($parts[0][0] . $parts[count($parts) - 1][0]);
            } else {
                $user->avatar_fallback = strtoupper(substr($name, 0, 2));
            }

            if (empty($user->avatar)) {
                $hash = md5(strtolower(trim($user->email)));
                $user->avatar = "https://www.gravatar.com/avatar/{$hash}?d=identicon&s=200";
            } elseif (!str_starts_with($user->avatar, 'http://') && !str_starts_with($user->avatar, 'https://')) {
                $path = ltrim($user->avatar, '/');

                if (str_starts_with($path, 'storage/')) {
                    $user->avatar = '/' . $path;
                } else {
                    $user->avatar = Storage::url($path);
                }
            }
        });

        return view('projects.members', [
            'project' => $project,
        ]);
    }

    public function updateMemberRole(Request $request, Project $project)
    {
        $this->ensureProjectOwner($project);

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'role' => ['required', 'string', 'max:100'],
        ]);

        $this->assignMemberRoleHandler->handle($project, (int) $data['user_id'], $data['role']);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật vai trò thành viên.',
            ]);
        }

        return back()->with('success', 'Đã cập nhật vai trò thành viên.');
    }

    public function importMembers(Request $request, Project $project)
    {
        $this->ensureProjectOwner($project);

        $data = $request->validate([
            'members' => ['required', 'array', 'min:1'],
            'members.*.email' => ['required', 'email', 'max:255'],
            'members.*.role' => ['nullable', 'string', 'max:100'],
        ]);

        $results = [];
        foreach ($data['members'] as $memberData) {
            $result = $this->addUserHandler->handle(
                auth()->id(),
                $project->id,
                $memberData['email'],
                $memberData['role'] ?? 'member'
            );
            if ($result['success']) {
                ActivityLogger::record(
                    projectId: $project->id,
                    action: ActivityLogger::ACTION_INVITED,
                    userId: Auth::id(),
                    subjectLabel: $memberData['email'],
                    meta: (string) ($memberData['role'] ?? 'member'),
                );
            }
            $results[] = $result;
        }

        $hasError = collect($results)->contains(fn ($r) => ! $r['success']);

        if ($hasError) {
            $errors = collect($results)
                ->filter(fn ($r) => ! $r['success'])
                ->map(fn ($r) => $r['error'])
                ->all();

            return response()->json([
                'success' => false,
                'message' => 'Một số thành viên không thể nhập.',
                'errors' => $errors,
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Nhập thành viên thành công.',
            'count' => count($results),
        ]);
    }

    public function removeMember(Request $request, Project $project)
    {
        $this->ensureProjectOwner($project);

        $data = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $project->users()->detach((int) $data['user_id']);

        ActivityLogger::record(
            projectId: $project->id,
            action: ActivityLogger::ACTION_REMOVED,
            userId: Auth::id(),
            subjectLabel: 'User #' . $data['user_id'],
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã xóa thành viên khỏi dự án.',
            ]);
        }

        return back()->with('success', 'Đã xóa thành viên khỏi dự án.');
    }

    public function inviteMember(Request $request, Project $project)
    {
        $this->ensureProjectOwner($project);

        $data = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'string', 'max:100'],
        ]);

        $result = $this->addUserHandler->handle(
            auth()->id(),
            $project->id,
            $data['email'],
            $data['role']
        );

        if (! $result['success']) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'error' => $result['error'],
                ], 422);
            }

            return back()->with('error', $result['error']);
        }

        ActivityLogger::record(
            projectId: $project->id,
            action: ActivityLogger::ACTION_INVITED,
            userId: Auth::id(),
            subjectLabel: $data['email'],
            meta: $data['role'],
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ]);
        }

        return back()->with('success', $result['message']);
    }

    public function storePermission(Request $request, Project $project): RedirectResponse
    {
        $this->ensureProjectOwner($project);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['in:manager,member,viewer'],
        ], [
            'name.required' => 'Vui lòng nhập tên quyền.',
            'roles.required' => 'Vui lòng chọn ít nhất một vai trò.',
        ]);

        $name = trim($data['name']);

        if ($project->permissions()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->exists()) {
            return back()->withErrors(['permission_name' => 'Quyền này đã tồn tại trong dự án.'])->withInput();
        }

        $roles = array_values(array_unique($data['roles']));

        $permission = ProjectPermission::create([
            'project_id' => $project->id,
            'name' => $name,
            'roles' => $roles,
        ]);

        $this->syncPermissionToMembers($project, $permission);

        return back()->with('success', 'Đã thêm quyền cho dự án.');
    }

    public function editPermissions(Project $project): View
    {
        $this->ensureProjectOwner($project);

        return view('projects.permissions', [
            'project' => $project->load('permissions'),
        ]);
    }

    public function updatePermission(Request $request, Project $project, ProjectPermission $permission): RedirectResponse
    {
        $this->ensureProjectOwner($project);
        $this->ensurePermissionBelongsToProject($project, $permission);

        $data = $this->validatePermission($request);
        $name = trim($data['name']);

        if ($project->permissions()->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])->where('id', '!=', $permission->id)->exists()) {
            return back()->withErrors(['permission_' . $permission->id => 'Tên quyền này đã tồn tại.'])->withInput();
        }

        $previousName = $permission->name;
        $permission->update([
            'name' => $name,
            'roles' => array_values(array_unique($data['roles'])),
        ]);
        $this->syncPermissionToMembers($project, $permission, $previousName);

        return back()->with('success', 'Đã cập nhật quyền.');
    }

    public function destroyPermission(Project $project, ProjectPermission $permission): RedirectResponse
    {
        $this->ensureProjectOwner($project);
        $this->ensurePermissionBelongsToProject($project, $permission);

        $this->syncPermissionToMembers($project, $permission, $permission->name, false);
        $permission->delete();

        return back()->with('success', 'Đã xóa quyền.');
    }

    private function validatePermission(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['in:manager,member,viewer'],
        ], [
            'name.required' => 'Vui lòng nhập tên quyền.',
            'roles.required' => 'Vui lòng chọn ít nhất một vai trò.',
        ]);
    }

    private function ensureProjectOwner(Project $project): void
    {
        if ($project->user_id !== Auth::id()) {
            abort(403, 'Chỉ chủ dự án mới có thể quản lý dự án này.');
        }
    }

    private function ensurePermissionBelongsToProject(Project $project, ProjectPermission $permission): void
    {
        if ($permission->project_id !== $project->id) {
            abort(404);
        }
    }

    private function ensureRoleBelongsToProject(Project $project, ProjectRole $role): void
    {
        if ($role->project_id !== $project->id) {
            abort(404);
        }
    }

    private function syncPermissionToMembers(Project $project, ProjectPermission $permission, ?string $previousName = null, bool $grant = true): void
    {
        $project->load('users');
        $previousName ??= $permission->name;

        foreach ($project->users as $member) {
            $currentPermissions = $member->pivot->permissions;
            if (is_string($currentPermissions)) {
                $currentPermissions = json_decode($currentPermissions, true);
            }

            $permissions = array_values(array_filter((array) $currentPermissions, fn ($name) => $name !== $previousName));
            if ($grant && in_array($member->pivot->role, $permission->roles ?? [], true)) {
                $permissions[] = $permission->name;
            }

            $project->users()->updateExistingPivot($member->id, [
                'permissions' => json_encode(array_values(array_unique($permissions))),
            ]);
        }
    }
}
