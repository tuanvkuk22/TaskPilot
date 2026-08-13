<?php

namespace ProjectsManager\Modules\Comments\Handlers;

use Illuminate\Database\Eloquent\Collection;
use ProjectsManager\Modules\Comments\Repositories\CommentRepository;

class ListCommentsHandler
{
    /**
     * Create a new handler instance.
     *
     * @param CommentRepository $repository
     */
    public function __construct(
        private readonly CommentRepository $repository
    ) {
    }

    /**
     * Execute list comments use case.
     *
     * @param int $userId
     *
     * @return Collection
     */
    public function handle(int $userId): Collection
    {
        return $this->repository->getCommentsForUser($userId);
    }
}
