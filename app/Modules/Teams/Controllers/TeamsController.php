<?php

namespace ProjectsManager\Modules\Teams\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use ProjectsManager\Http\Controllers\Controller;
use ProjectsManager\Modules\Teams\Handlers\InviteTeamMemberHandler;
use ProjectsManager\Modules\Teams\Handlers\ListTeamsHandler;
use ProjectsManager\Modules\Teams\Handlers\ShowTeamHandler;
use ProjectsManager\Modules\Teams\Handlers\StoreTeamHandler;
use ProjectsManager\Team;

class TeamsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param ListTeamsHandler $listTeamsHandler
     * @param StoreTeamHandler $storeTeamHandler
     * @param ShowTeamHandler $showTeamHandler
     * @param InviteTeamMemberHandler $inviteHandler
     */
    public function __construct(
        private readonly ListTeamsHandler $listTeamsHandler,
        private readonly StoreTeamHandler $storeTeamHandler,
        private readonly ShowTeamHandler $showTeamHandler,
        private readonly InviteTeamMemberHandler $inviteHandler
    ) {
    }

    /**
     * Display a listing of teams.
     *
     * @return View
     */
    public function index(): View
    {
        $teams = $this->listTeamsHandler->handle(Auth::id());

        return view('teams.index', ['teams' => $teams]);
    }

    /**
     * Show the form for creating a new team.
     *
     * @return View
     */
    public function create(): View
    {
        return view('teams.create');
    }

    /**
     * Store a newly created team in storage.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
            'team_type' => ['nullable', 'string', 'in:technical,marketing,business,other'],
            'team_type_custom' => ['nullable', 'string', 'max:100', 'required_if:team_type,other'],
            'members' => ['nullable', 'string'],
        ]);

        $result = $this->storeTeamHandler->handle(Auth::id(), $request->all());

        if (! $result['success']) {
            return back()->withInput()->withErrors([
                'team' => $result['error'],
            ]);
        }

        return redirect()->route('teams.show', $result['team'])
            ->with('success', 'Đội nhóm đã được tạo thành công.');
    }

    /**
     * Display the specified team.
     *
     * @param Team $team
     *
     * @return View
     */
    public function show(Team $team): View
    {
        $team = $this->showTeamHandler->handle($team);

        return view('teams.show', ['team' => $team]);
    }

    public function edit(Team $team): View
    {
        abort_unless($team->user_id === Auth::id(), 403);

        return view('teams.edit', ['team' => $team]);
    }

    public function update(Request $request, Team $team): RedirectResponse
    {
        abort_unless($team->user_id === Auth::id(), 403);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
        $team->update($data);

        return redirect()->route('teams.show', $team)->with('success', 'Đội nhóm đã được cập nhật.');
    }

    public function destroy(Team $team): RedirectResponse
    {
        abort_unless($team->user_id === Auth::id(), 403);
        $team->users()->detach();
        $team->delete();

        return redirect()->route('teams.index')->with('success', 'Đội nhóm đã được xóa.');
    }

    /**
     * Invite members to team.
     *
     * @param Request $request
     * @param Team $team
     *
     * @return RedirectResponse
     */
    public function invite(Request $request, Team $team): RedirectResponse
    {
        abort_unless($team->user_id === Auth::id() || $team->users()->whereKey(Auth::id())->wherePivot('role', 'owner')->exists(), 403);
        $result = $this->inviteHandler->handle($team, $request->input('members'));

        if (! $result['success']) {
            return back()->withErrors([
                'team' => $result['error'],
            ]);
        }

        return back()->with('success', $result['message']);
    }
}
