<?php

namespace ProjectsManager\Modules\Comments\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use ProjectsManager\Comment;
use ProjectsManager\Http\Controllers\Controller;
use ProjectsManager\Modules\Comments\Handlers\ListCommentsHandler;
use ProjectsManager\Modules\Comments\Handlers\ShowCommentHandler;
use ProjectsManager\Modules\Comments\Handlers\StoreCommentHandler;

class CommentsController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param ListCommentsHandler $listCommentsHandler
     * @param StoreCommentHandler $storeCommentHandler
     * @param ShowCommentHandler $showCommentHandler
     */
    public function __construct(
        private readonly ListCommentsHandler $listCommentsHandler,
        private readonly StoreCommentHandler $storeCommentHandler,
        private readonly ShowCommentHandler $showCommentHandler
    ) {
    }

    /**
     * Display a listing of comments.
     *
     * @return View|RedirectResponse
     */
    public function index(): View|RedirectResponse
    {
        if (! Auth::check()) {
            return view('auth.login');
        }

        $comments = $this->listCommentsHandler->handle(Auth::id());

        return view('comments.index', ['comments' => $comments]);
    }

    /**
     * Store a newly created comment in storage.
     *
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        if (! Auth::check()) {
            return back()->withInput()->with('errors', 'Error creating new comment');
        }

        $request->validate([
            'body' => ['required', 'string'],
            'commentable_type' => ['required', 'string'],
            'commentable_id' => ['required', 'integer'],
        ]);

        $comment = $this->storeCommentHandler->handle(Auth::id(), $request->all());

        if ($comment) {
            return back();
        }

        return back()->withInput()->with('errors', 'Error creating new comment');
    }

    /**
     * Display the specified comment.
     *
     * @param Comment $comment
     *
     * @return View
     */
    public function show(Comment $comment): View
    {
        $comment = $this->showCommentHandler->handle($comment);

        return view('comments.show', ['comment' => $comment]);
    }
}
