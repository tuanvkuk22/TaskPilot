<?php

use Illuminate\Support\Facades\Route;
use ProjectsManager\Http\Controllers\Auth\ForgotPasswordController;
use ProjectsManager\Http\Controllers\Auth\LoginController;
use ProjectsManager\Http\Controllers\Auth\RegisterController;
use ProjectsManager\Http\Controllers\Auth\ResetPasswordController;
use ProjectsManager\Http\Controllers\Auth\SocialAuthController;
use ProjectsManager\Modules\Comments\Controllers\CommentsController;
use ProjectsManager\Modules\Companies\Controllers\CompaniesController;
use ProjectsManager\Modules\Home\Controllers\HomeController;
use ProjectsManager\Modules\Meetings\Controllers\MeetingsController;
use ProjectsManager\Modules\Projects\Controllers\ProjectsController;
use ProjectsManager\Modules\Projects\Controllers\ProjectToolsController;
use ProjectsManager\Modules\Projects\Controllers\ProjectDocumentsController;
use ProjectsManager\Modules\Projects\Controllers\ProjectFormsController;
use ProjectsManager\Modules\Projects\Controllers\ProjectDevelopmentController;
use ProjectsManager\Modules\Tasks\Controllers\SprintsController;
use ProjectsManager\Modules\Tasks\Controllers\TasksController;
use ProjectsManager\Modules\Teams\Controllers\TeamsController;
use ProjectsManager\Modules\Notifications\Controllers\NotificationController;
use ProjectsManager\Modules\Profile\Controllers\SkillsController;
use ProjectsManager\Modules\Profile\Controllers\ProfileController;
use ProjectsManager\Http\Controllers\QuickSearchController;

Route::view('/', 'welcome')->name('welcome');

Route::middleware('guest')->group(function (): void {
    Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('login', [LoginController::class, 'login']);
    Route::get('register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [RegisterController::class, 'register']);
    Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');
    Route::get('auth/google', [SocialAuthController::class, 'redirectToProvider'])->name('google.redirect');
    Route::get('auth/google/callback', [SocialAuthController::class, 'handleProviderCallback'])->name('google.callback');
});
Route::post('logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('home', [HomeController::class, 'index'])->name('home');
    Route::get('api/quick-search', [QuickSearchController::class, 'search'])->name('quick-search');
    Route::view('huong-dan', 'guide')->name('guide');
    Route::get('profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('skills', [ProfileController::class, 'index'])->name('skills.index');
    Route::get('api/skills', [SkillsController::class, 'getSkills'])->name('skills.api.get');
    Route::post('api/skills', [SkillsController::class, 'store'])->name('skills.api.store');
    Route::delete('api/skills/{id}', [SkillsController::class, 'destroy'])->name('skills.api.destroy');
    Route::resource('companies', CompaniesController::class);

    Route::get('projects/role-permissions', [ProjectsController::class, 'availablePermissions'])->name('projects.role.permissions');

    Route::resource('projects', ProjectsController::class);
    Route::get('projects/create/{companyId?}', [ProjectsController::class, 'create'])->name('projects.create.for-company');
    Route::post('projects/adduser', [ProjectsController::class, 'adduser'])->name('projects.adduser')->middleware('can.project:manage_members');
    Route::post('projects/quick-category', [ProjectsController::class, 'storeQuickCategory'])->name('projects.categories.store');
    Route::post('projects/quick-company', [ProjectsController::class, 'storeQuickCompany'])->name('projects.companies.store');
    Route::post('projects/{project}/archive', [ProjectsController::class, 'archive'])->name('projects.archive');
    Route::get('projects/{project}/board', [ProjectsController::class, 'board'])->name('projects.board');
    Route::get('projects/{project}/timeline', [ProjectsController::class, 'timeline'])->name('projects.timeline');
    Route::get('projects/{project}/roadmap', [ProjectsController::class, 'roadmap'])->name('projects.roadmap');
    Route::get('projects/{project}/permissions', [ProjectsController::class, 'editPermissions'])->name('projects.permissions.index')->middleware('can.project:manage_project');
    Route::post('projects/{project}/permissions', [ProjectsController::class, 'storePermission'])->name('projects.permissions.store')->middleware('can.project:manage_project');
    Route::put('projects/{project}/permissions/{permission}', [ProjectsController::class, 'updatePermission'])->name('projects.permissions.update')->middleware('can.project:manage_project');
    Route::delete('projects/{project}/permissions/{permission}', [ProjectsController::class, 'destroyPermission'])->name('projects.permissions.destroy')->middleware('can.project:manage_project');

    Route::get('projects/{project}/roles', [ProjectsController::class, 'roles'])->name('projects.roles.index')->middleware('can.project:manage_project');
    Route::post('projects/{project}/roles', [ProjectsController::class, 'storeRole'])->name('projects.roles.store')->middleware('can.project:manage_project');
    Route::put('projects/{project}/roles/{role}', [ProjectsController::class, 'updateRole'])->name('projects.roles.update')->middleware('can.project:manage_project');
    Route::delete('projects/{project}/roles/{role}', [ProjectsController::class, 'destroyRole'])->name('projects.roles.destroy')->middleware('can.project:manage_project');

    Route::get('projects/{project}/tools/documents', [ProjectToolsController::class, 'documents'])->name('projects.tools.documents');
    Route::get('projects/{project}/tools/budgets', [ProjectToolsController::class, 'budgets'])->name('projects.tools.budgets');
    Route::get('projects/{project}/tools/chat', [ProjectToolsController::class, 'chat'])->name('projects.tools.chat');
    Route::post('projects/{project}/tools', [ProjectToolsController::class, 'store'])->name('projects.tools.store');

    Route::post('tasks/recommend-assignee', [\ProjectsManager\Modules\Tasks\Controllers\AiRecommendationController::class, 'recommend'])->name('tasks.recommend-assignee');
    Route::post('tasks/recommend-assignee-batch', [\ProjectsManager\Modules\Tasks\Controllers\AiRecommendationController::class, 'recommendBatch'])->name('tasks.recommend-assignee-batch');
    Route::post('tasks/ai-assign-batch', [\ProjectsManager\Modules\Tasks\Controllers\AiRecommendationController::class, 'assignBatch'])->name('tasks.ai-assign-batch');
    Route::post('tasks/ai-test-reset', [\ProjectsManager\Modules\Tasks\Controllers\AiRecommendationController::class, 'resetTestAssignments'])->name('tasks.ai-test-reset');
    Route::post('tasks/ai-feedback', [\ProjectsManager\Modules\Tasks\Controllers\AiRecommendationController::class, 'feedback'])->name('tasks.ai-feedback');
    Route::get('tasks/form-data', [TasksController::class, 'formData'])->name('tasks.form-data');
    Route::resource('tasks', TasksController::class);
    Route::match(['post', 'patch', 'put'], 'tasks/{task}/move', [TasksController::class, 'move'])->name('tasks.move');
    Route::post('tasks/{task}/worklog', [TasksController::class, 'worklog'])->name('tasks.worklog');

    Route::get('projects/{project}/sprints', [SprintsController::class, 'index'])->name('sprints.index');
    Route::post('projects/{project}/sprints', [SprintsController::class, 'store'])->name('sprints.store');

    Route::post('sprints/{sprint}/start', [SprintsController::class, 'start'])->name('sprints.start');
    Route::post('sprints/{sprint}/close', [SprintsController::class, 'close'])->name('sprints.close');

    Route::resource('teams', TeamsController::class);
    Route::post('teams/{team}/invite', [TeamsController::class, 'invite'])->name('teams.invite');
    Route::resource('comments', CommentsController::class)->only(['index', 'store', 'show']);

    Route::get('projects/{project}/meetings', [MeetingsController::class, 'index'])->name('meetings.index');
    Route::post('projects/{project}/meetings', [MeetingsController::class, 'store'])->name('meetings.store');

    Route::get('meetings/{meeting}', [MeetingsController::class, 'show'])->name('meetings.show');
    Route::get('meetings/{meeting}/archive', [MeetingsController::class, 'archive'])->name('meetings.archive');
    Route::get('meetings/{meeting}/token', [MeetingsController::class, 'token'])->name('meetings.token');
    Route::get('meetings/{meeting}/tasks', [MeetingsController::class, 'tasks'])->name('meetings.tasks');
    Route::get('meetings/{meeting}/messages', [MeetingsController::class, 'messages'])->name('meetings.messages');
    Route::post('meetings/{meeting}/messages', [MeetingsController::class, 'message'])->name('meetings.messages.store');
    Route::put('meetings/{meeting}/messages/{message}', [MeetingsController::class, 'updateMessage'])->name('meetings.messages.update');
    Route::delete('meetings/{meeting}/messages/{message}', [MeetingsController::class, 'deleteMessage'])->name('meetings.messages.destroy');
    Route::post('meetings/{meeting}/messages/{message}/task', [MeetingsController::class, 'taskFromMessage'])->name('meetings.messages.task');
    Route::put('meetings/{meeting}/note', [MeetingsController::class, 'note'])->name('meetings.note');
    Route::put('meetings/{meeting}/minutes', [MeetingsController::class, 'minutes'])->name('meetings.minutes');
    Route::post('meetings/{meeting}/recordings', [MeetingsController::class, 'recording'])->name('meetings.recording');
    Route::get('meetings/{meeting}/file/{path}', [MeetingsController::class, 'file'])->where('path', '.*')->name('meetings.file');
    Route::post('meetings/{meeting}/presence', [MeetingsController::class, 'presence'])->name('meetings.presence');
    Route::get('meetings/{meeting}/presence', [MeetingsController::class, 'presenceLogs'])->name('meetings.presence.logs');
    Route::get('meetings/{meeting}/live-presence', [MeetingsController::class, 'livePresence'])->name('meetings.live-presence');
    Route::post('meetings/{meeting}/end', [MeetingsController::class, 'end'])->name('meetings.end');

    Route::get('projects/{project}/members', [ProjectsController::class, 'members'])->name('projects.members.index')->middleware('can.project:manage_members');
    Route::post('projects/{project}/members/role', [ProjectsController::class, 'updateMemberRole'])->name('projects.members.role.update')->middleware('can.project:manage_members');
    Route::post('projects/{project}/members/invite', [ProjectsController::class, 'inviteMember'])->name('projects.members.invite')->middleware('can.project:manage_members');
    Route::post('projects/{project}/members/import', [ProjectsController::class, 'importMembers'])->name('projects.members.import')->middleware('can.project:manage_members');
    Route::delete('projects/{project}/members', [ProjectsController::class, 'removeMember'])->name('projects.members.destroy')->middleware('can.project:manage_members');

    // Documents
    Route::get('projects/{project}/documents', [ProjectDocumentsController::class, 'index'])->name('projects.documents.index');
    Route::post('projects/{project}/documents', [ProjectDocumentsController::class, 'store'])->name('projects.documents.store');
    Route::delete('projects/{project}/documents/{document}', [ProjectDocumentsController::class, 'destroy'])->name('projects.documents.destroy');

    // Forms
    Route::get('projects/{project}/forms', [ProjectFormsController::class, 'index'])->name('projects.forms.index');
    Route::post('projects/{project}/forms', [ProjectFormsController::class, 'storeForm'])->name('projects.forms.store');
    Route::get('projects/{project}/forms/{form}', [ProjectFormsController::class, 'show'])->name('projects.forms.show');
    Route::post('projects/{project}/forms/{form}/responses', [ProjectFormsController::class, 'submitResponse'])->name('projects.forms.responses.store');
    Route::delete('projects/{project}/forms/{form}', [ProjectFormsController::class, 'destroyForm'])->name('projects.forms.destroy');

    // Development
    Route::get('projects/{project}/development', [ProjectDevelopmentController::class, 'index'])->name('projects.development.index');
    Route::post('projects/{project}/development', [ProjectDevelopmentController::class, 'store'])->name('projects.development.store');
    Route::delete('projects/{project}/development/{link}', [ProjectDevelopmentController::class, 'destroy'])->name('projects.development.destroy');

    Route::name('notifications.')->middleware('auth')->group(function (): void {
        Route::get('notifications', [NotificationController::class, 'index'])->name('index');
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
        Route::get('notifications/recent', [NotificationController::class, 'recentNotifications'])->name('recent');
        Route::post('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
        Route::post('notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
        Route::delete('notifications/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    });
});
