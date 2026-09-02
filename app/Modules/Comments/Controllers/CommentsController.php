<?php

namespace ProjectsManager\Modules\Comments\Controllers;

use Illuminate\Http\JsonResponse;
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
     * @param Request $request
     *
     * @return View|RedirectResponse|JsonResponse
     */
    public function index(Request $request): View|RedirectResponse|JsonResponse
    {
        if ($request->wantsJson() || $request->ajax()) {
            $commentableId = $request->input('commentable_id');
            $commentableType = $request->input('commentable_type', 'ProjectsManager\Task');

            $query = Comment::query()->with('user');
            if ($commentableId) {
                $query->where('commentable_id', $commentableId)
                    ->where('commentable_type', $commentableType);
            }

            $comments = $query->latest()->get();

            return response()->json(['success' => true, 'comments' => $comments]);
        }

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
     * @return RedirectResponse|JsonResponse
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        if (! Auth::check()) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
            }

            return back()->withInput()->with('errors', 'Error creating new comment');
        }

        $request->validate([
            'body' => ['required', 'string'],
            'commentable_type' => ['required', 'string'],
            'commentable_id' => ['required', 'integer'],
        ]);

        $comment = $this->storeCommentHandler->handle(Auth::id(), $request->all());

        if ($comment) {
            $comment->load('user');
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'comment' => $comment]);
            }

            return back();
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => false, 'message' => 'Error creating new comment'], 500);
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
