<?php

namespace ProjectsManager\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use ProjectsManager\Team;
use ProjectsManager\User;

class TeamInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @param Team $team
     * @param User|null $inviter
     */
    public function __construct(
        public readonly Team $team,
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
        return $this->subject('Lời mời tham gia đội nhóm ' . $this->team->name . ' trên TaskPilot')
            ->view('emails.team_invitation');
    }
}
