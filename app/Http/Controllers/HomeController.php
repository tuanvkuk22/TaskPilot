<?php

namespace ProjectsManager\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use ProjectsManager\Project;
use ProjectsManager\Task;
use ProjectsManager\Company;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('home', [
            'totalProjects' => Project::count(),
            'totalTasks' => Task::count(),
            'totalCompanies' => Company::count(),
            'userTasks' => Auth::user()->tasks()->count(),
        ]);
    }
}

