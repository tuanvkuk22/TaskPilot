<?php

namespace ProjectsManager\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use ProjectsManager\Observers\CommentObserver;
use ProjectsManager\Observers\ProjectObserver;
use ProjectsManager\Observers\SprintObserver;
use ProjectsManager\Observers\TaskObserver;
use ProjectsManager\Observers\WorklogObserver;
use ProjectsManager\Comment;
use ProjectsManager\Project;
use ProjectsManager\Sprint;
use ProjectsManager\Task;
use ProjectsManager\Worklog;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);

        Task::observe(TaskObserver::class);
        Comment::observe(CommentObserver::class);
        Sprint::observe(SprintObserver::class);
        Project::observe(ProjectObserver::class);
        Worklog::observe(WorklogObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}