<?php

namespace ProjectsManager\Modules\Comments\Handlers;

use ProjectsManager\Comment;
use ProjectsManager\Modules\Comments\Repositories\CommentRepository;

class StoreCommentHandler
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
     * Execute store comment use case.
     *
     * @param int $userId
     * @param array $data
     *
     * @return Comment
     */
    public function handle(int $userId, array $data): Comment
    {
        return $this->repository->create([
            'body' => $data['body'] ?? '',
            'url' => $data['url'] ?? null,
            'commentable_type' => $data['commentable_type'] ?? '',
            'commentable_id' => $data['commentable_id'] ?? 0,
            'user_id' => $userId,
        ]);
    }
}
