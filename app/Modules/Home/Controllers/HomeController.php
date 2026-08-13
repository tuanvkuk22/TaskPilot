<?php

namespace ProjectsManager\Modules\Home\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use ProjectsManager\Http\Controllers\Controller;
use ProjectsManager\Modules\Home\Handlers\GetHomeDashboardHandler;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param GetHomeDashboardHandler $dashboardHandler
     */
    public function __construct(
        private readonly GetHomeDashboardHandler $dashboardHandler
    ) {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return View
     */
    public function index(): View
    {
        $data = $this->dashboardHandler->handle(Auth::user());

        return view('home', $data);
    }
}
