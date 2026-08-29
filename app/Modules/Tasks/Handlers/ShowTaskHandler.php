<?php

namespace ProjectsManager\Modules\Tasks\Handlers;

use ProjectsManager\Task;

class ShowTaskHandler
{
    /**
     * Execute show task use case.
     *
     * @param Task $task
     *
     * @return Task
     */
    public function handle(Task $task): Task
    {
        $task->load('project', 'user', 'assignee', 'reporter', 'sprintRelation', 'parent', 'children');

        return $task;
    }
}
