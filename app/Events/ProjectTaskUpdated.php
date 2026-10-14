<?php

namespace ProjectsManager\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use ProjectsManager\Task;

class ProjectTaskUpdated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    public function __construct(public Task $task, public string $source = 'task') {}

    public function broadcastOn(): array { return [new PrivateChannel('project.'.$this->task->project_id)]; }
    public function broadcastAs(): string { return 'task.updated'; }
    public function broadcastWith(): array { return ['source'=>$this->source, 'task'=>$this->task->fresh(['assignee','reporter'])->only(['id','project_id','name','status','priority','assignee_id','reporter_id','sprint_id','updated_at'])]; }
}
