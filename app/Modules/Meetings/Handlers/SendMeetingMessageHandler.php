<?php

namespace ProjectsManager\Modules\Meetings\Handlers;

use ProjectsManager\Meeting;
use ProjectsManager\MeetingMessage;
use ProjectsManager\Modules\Meetings\Repositories\MeetingRepository;
use ProjectsManager\User;

class SendMeetingMessageHandler
{
    public function __construct(private readonly MeetingRepository $repository) {}

    public function handle(User $user, Meeting $meeting, array $data, array $mentionIds = []): MeetingMessage
    {
        $message = $this->repository->createMessage([
            'meeting_id' => $meeting->id,
            'user_id' => $user->id,
            'recipient_id' => $data['recipient_id'] ?? null,
            'body' => $data['body'] ?? null,
            'attachment_path' => $data['attachment_path'] ?? null,
            'attachment_name' => $data['attachment_name'] ?? null,
            'attachment_mime' => $data['attachment_mime'] ?? null,
            'attachment_size' => $data['attachment_size'] ?? null,
        ]);
        if ($mentionIds) $message->mentions()->sync($mentionIds);
        foreach ($data['attachments'] ?? [] as $attachment) $message->attachments()->create($attachment);
        return $message->load(['user', 'mentions', 'sourceTask', 'attachments']);
    }
}
