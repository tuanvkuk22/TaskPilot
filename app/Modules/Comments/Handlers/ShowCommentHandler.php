<?php

namespace ProjectsManager\Modules\Comments\Handlers;

use ProjectsManager\Comment;
use ProjectsManager\Modules\Comments\Repositories\CommentRepository;

class ShowCommentHandler
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
     * Execute show comment use case.
     *
     * @param Comment $comment
     *
     * @return Comment
     */
    public function handle(Comment $comment): Comment
    {
        return $this->repository->findById($comment->id) ?? $comment;
    }
}
