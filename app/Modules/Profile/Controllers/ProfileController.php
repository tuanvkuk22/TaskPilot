<?php

namespace ProjectsManager\Modules\Profile\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use ProjectsManager\Http\Controllers\Controller;
use ProjectsManager\Task;
use ProjectsManager\UserSkill;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the personal profile page (including Skill Profile).
     */
    public function index(): View
    {
        $user = Auth::user();
        $userSkills = $user->skills()->orderBy('skill_name')->get();

        // User activity statistics
        $assignedTasksCount = Task::where('assignee_id', $user->id)->count();
        $completedTasksCount = Task::where('assignee_id', $user->id)
            ->whereIn('status', ['done', 'completed', 'closed', 'resolved'])
            ->count();
        $inProgressTasksCount = Task::where('assignee_id', $user->id)
            ->whereIn('status', ['in_progress', 'doing', 'testing'])
            ->count();

        // Open unassigned tasks for AI recommendation preview
        $matchingTasks = Task::query()
            ->with('project')
            ->whereNull('assignee_id')
            ->whereNotIn('status', ['done', 'closed', 'completed', 'resolved'])
            ->latest()
            ->limit(5)
            ->get();

        $stats = [
            'assigned_tasks' => $assignedTasksCount,
            'completed_tasks' => $completedTasksCount,
            'in_progress_tasks' => $inProgressTasksCount,
            'skills_count' => $userSkills->count(),
        ];

        return view('profile.index', [
            'user' => $user,
            'stats' => $stats,
            'initialSkills' => $userSkills,
            'matchingTasks' => $matchingTasks,
        ]);
    }
}
