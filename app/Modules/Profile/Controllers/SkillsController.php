<?php

namespace ProjectsManager\Modules\Profile\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use ProjectsManager\Http\Controllers\Controller;
use ProjectsManager\Task;
use ProjectsManager\UserSkill;

class SkillsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display the skills profile page.
     */
    public function index(): View
    {
        $user = Auth::user();
        $userSkills = $user->skills()->orderBy('skill_name')->get();

        // Get open unassigned tasks or matching tasks for AI insight preview
        $matchingTasks = Task::query()
            ->with('project')
            ->whereNull('assignee_id')
            ->whereNotIn('status', ['done', 'closed', 'completed', 'resolved'])
            ->latest()
            ->limit(5)
            ->get();

        return view('profile.skills.index', [
            'user' => $user,
            'initialSkills' => $userSkills,
            'matchingTasks' => $matchingTasks,
        ]);
    }

    /**
     * Get list of skills via API.
     */
    public function getSkills(): JsonResponse
    {
        $user = Auth::user();
        $skills = $user->skills()->orderBy('skill_name')->get();

        // Preset popular skills in software engineering & project management
        $presetSkills = [
            'PHP', 'Laravel', 'Vue.js', 'JavaScript', 'TypeScript', 'HTML/CSS',
            'React', 'Node.js', 'Python', 'MySQL', 'PostgreSQL', 'Docker',
            'REST API', 'DevOps', 'UI/UX Design', 'QA Testing', 'System Architecture',
            'Agile/Scrum', 'Git', 'CI/CD', 'Security', 'Data Analysis'
        ];

        return response()->json([
            'success' => true,
            'skills' => $skills,
            'presetSkills' => $presetSkills,
        ]);
    }

    /**
     * Save (create or update) a user skill.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'skill_name' => 'required|string|max:100',
            'proficiency_level' => 'required|integer|min:1|max:5',
            'years_experience' => 'nullable|integer|min:0|max:50',
        ]);

        $user = Auth::user();
        $skillName = trim($request->input('skill_name'));

        $skill = UserSkill::updateOrCreate(
            [
                'user_id' => $user->id,
                'skill_name' => $skillName,
            ],
            [
                'proficiency_level' => (int) $request->input('proficiency_level'),
                'years_experience' => $request->input('years_experience') !== null ? (int) $request->input('years_experience') : 1,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Đã lưu kỹ năng thành công!',
            'skill' => $skill,
        ]);
    }

    /**
     * Delete a user skill.
     */
    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();
        $skill = UserSkill::where('user_id', $user->id)->where('id', $id)->first();

        if (!$skill) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy kỹ năng.',
            ], 404);
        }

        $skill->delete();

        return response()->json([
            'success' => true,
            'message' => 'Đã xóa kỹ năng thành công.',
        ]);
    }
}
