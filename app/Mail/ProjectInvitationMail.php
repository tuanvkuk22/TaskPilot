<?php

namespace ProjectsManager\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use ProjectsManager\Project;
use ProjectsManager\User;

class ProjectInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param Project $project
     * @param User|null $inviter
     */
    public function __construct(
        public readonly Project $project,
        public readonly ?User $inviter = null
    ) {
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Lời mời tham gia dự án ' . $this->project->name . ' trên TaskPilot')
            ->view('emails.project_invitation');
    }
}
