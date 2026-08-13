<?php

namespace ProjectsManager\Modules\Tasks\Handlers;

use ProjectsManager\Modules\Tasks\Repositories\TaskRepository;
use ProjectsManager\Task;

class DeleteTaskHandler
{
    /**
     * Create a new handler instance.
     *
     * @param TaskRepository $repository
     */
    public function __construct(
        private readonly TaskRepository $repository
    ) {
    }

    /**
     * Execute task deletion use case.
     *
     * @param Task $task
     *
     * @return bool|null
     */
    public function handle(Task $task): ?bool
    {
        return $this->repository->delete($task);
    }
}
