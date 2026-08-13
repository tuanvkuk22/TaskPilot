<?php

namespace ProjectsManager\Modules\Comments\Repositories;

use Illuminate\Database\Eloquent\Collection;
use ProjectsManager\Comment;

class CommentRepository
{
    /**
     * Get user comments.
     *
     * @param int $userId
     *
     * @return Collection
     */
    public function getCommentsForUser(int $userId): Collection
    {
        return Comment::where('user_id', $userId)->get();
    }

    /**
     * Create a new comment.
     *
     * @param array $data
     *
     * @return Comment
     */
    public function create(array $data): Comment
    {
        return Comment::create($data);
    }

    /**
     * Find comment by ID.
     *
     * @param int $id
     *
     * @return Comment|null
     */
    public function findById(int $id): ?Comment
    {
        return Comment::find($id);
    }
}
