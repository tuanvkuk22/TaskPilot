<?php

namespace ProjectsManager\Modules\Projects\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use ProjectsManager\Http\Controllers\Controller;
use ProjectsManager\Modules\Projects\Handlers\AddUserToProjectHandler;
use ProjectsManager\Modules\Projects\Handlers\ArchiveProjectHandler;
use ProjectsManager\Modules\Projects\Handlers\DeleteProjectHandler;
use ProjectsManager\Modules\Projects\Handlers\GetProjectFormDataHandler;
use ProjectsManager\Modules\Projects\Handlers\ListProjectsHandler;
use ProjectsManager\Modules\Projects\Handlers\ShowProjectHandler;
use ProjectsManager\Modules\Projects\Handlers\StoreProjectHandler;
use ProjectsManager\Modules\Projects\Handlers\StoreQuickCategoryHandler;
use ProjectsManager\Modules\Projects\Handlers\UpdateProjectHandler;
use ProjectsManager\Modules\Companies\Handlers\StoreCompanyHandler;
use ProjectsManager\Project;
use ProjectsManager\ProjectPermission;

class ProjectsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param ListProjectsHandler $listProjectsHandler
     * @param GetProjectFormDataHandler $formDataHandler
     * @param StoreProjectHandler $storeProjectHandler
     * @param ShowProjectHandler $showProjectHandler
     * @param UpdateProjectHandler $updateProjectHandler
     * @param ArchiveProjectHandler $archiveProjectHandler
     * @param DeleteProjectHandler $deleteProjectHandler
     * @param AddUserToProjectHandler $addUserHandler
     * @param StoreQuickCategoryHandler $quickCategoryHandler
     */
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
        private readonly StoreCompanyHandler $storeCompanyHandler
    ) {
    }

    /**
     * Display a listing of projects.
     *
     * @param Request $request
     *
     * @return View|RedirectResponse
     */
    public function index(Request $request): View|RedirectResponse
    {
        if (! Auth::check()) {
            return view('auth.login');
        }

        $status = $request->query('status');
        $projects = $this->listProjectsHandler->handle(Auth::id(), $status);

        return view('projects.index', [
            'projects' => $projects,
            'status' => $status,
        ]);
    }

    public function board(Project $project): View
    {
        $project->load(['tasks.assignee', 'tasks.project']);

        return view('projects.board', ['project' => $project]);
    }

    public function timeline(Project $project): View
    {
        $project->load(['tasks.assignee']);

        return view('projects.timeline', ['project' => $project]);
    }

    public function roadmap(Project $project): View
    {
        $project->load(['tasks.assignee']);

        return view('projects.roadmap', ['project' => $project]);
    }

    /**
     * Show the form for creating a new project.
     *
     * @param mixed $companyId
     *
     * @return View
     */
    public function create(mixed $companyId = null): View
    {
        $formData = $this->formDataHandler->handle(Auth::id(), $companyId);

        return view('projects.create', $formData);
    }

    /**
     * Create quick project category via AJAX.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
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

    /**
     * Create a company/customer directly from the project form.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
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

    /**
     * Store a newly created project in storage.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
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
        ]);

        $result = $this->storeProjectHandler->handle(Auth::id(), $request->all());

        if (! $result['success']) {
            return back()->withInput()->withErrors(['project' => $result['error']]);
        }

        $redirect = redirect()->route('projects.show', ['project' => $result['project']->id]);

        if (isset($result['warning'])) {
            return $redirect->withErrors(['project' => $result['warning']]);
        }

        return $redirect->with('success', 'Dự án đã được tạo thành công.');
    }

    /**
     * Display the specified project.
     *
     * @param Project $project
     *
     * @return View
     */
    public function show(Project $project): View
    {
        $project = $this->showProjectHandler->handle($project);

        return view('projects.show', [
            'project' => $project,
            'comments' => $project->comments,
        ]);
    }

    /**
     * Show the form for editing the specified project.
     *
     * @param Project $project
     *
     * @return View
     */
    public function edit(Project $project): View
    {
        return view('projects.edit', [
            'project' => $project,
        ]);
    }

    /**
     * Update the specified project in storage.
     *
     * @param Request $request
     * @param Project $project
     *
     * @return RedirectResponse
     */
    public function update(Request $request, Project $project): RedirectResponse
    {
        $updated = $this->updateProjectHandler->handle($project, $request->all());

        if ($updated) {
            return redirect()->route('projects.show', ['project' => $project->id])
                ->with('success', 'Project updated successfully.');
        }

        return back()->withInput();
    }

    /**
     * Archive the specified project.
     *
     * @param Project $project
     *
     * @return RedirectResponse
     */
    public function archive(Project $project): RedirectResponse
    {
        $this->archiveProjectHandler->handle($project);

        return redirect()->route('projects.index')
            ->with('success', 'Project archived successfully.');
    }

    /**
     * Remove the specified project from storage.
     *
     * @param Project $project
     *
     * @return RedirectResponse
     */
    public function destroy(Project $project): RedirectResponse
    {
        $deleted = $this->deleteProjectHandler->handle($project);

        if ($deleted) {
            return redirect()->route('projects.index')
                ->with('success', 'Project deleted successfully.');
        }

        return back()->withInput()->with('error', 'Project could not be deleted.');
    }

    /**
     * Add user to specified project.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
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

        return back()->with('success', $result['message']);
    }

    /**
     * Add a project permission and assign the roles that receive it.
     */
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

        // Apply the new permission to current members whose project role is selected.
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
            abort(403, 'Chỉ chủ dự án mới có thể quản lý quyền.');
        }
    }

    private function ensurePermissionBelongsToProject(Project $project, ProjectPermission $permission): void
    {
        if ($permission->project_id !== $project->id) {
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
