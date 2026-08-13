<?php

namespace ProjectsManager\Modules\Teams\Handlers;

use ProjectsManager\Team;

class ShowTeamHandler
{
    /**
     * Execute show team details use case.
     *
     * @param Team $team
     *
     * @return Team
     */
    public function handle(Team $team): Team
    {
        $team->load('users');

        return $team;
    }
}
