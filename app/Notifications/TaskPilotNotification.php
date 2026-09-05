<?php

namespace ProjectsManager\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use ProjectsManager\User;

class TaskPilotNotification extends Notification
{
    use Queueable;

    public function __construct(
        public readonly string $eventType,
        public readonly string $title,
        public readonly string $message,
        public readonly ?array $data = null,
        public readonly ?string $url = null,
        public readonly ?User $actor = null,
    ) {
    }

    public function via($notifiable): array
    {
        $channels = [];

        if ($notifiable->in_app_notifications ?? true) {
            $channels[] = 'database';
        }

        if ($notifiable->email_notifications ?? true) {
            $channels[] = 'mail';
        }

        if (empty($channels)) {
            return ['database'];
        }

        return $channels;
    }

    public function toMail($notifiable): MailMessage
    {
        $mailMessage = (new MailMessage())
            ->subject('TaskPilot - ' . $this->title)
            ->greeting('Xin chào ' . ($notifiable->name ?? 'bạn') . ',')
            ->line($this->message)
            ->action('Xem chi tiết', $this->url ?? url('/'));

        if ($this->actor) {
            $mailMessage->line('Thực hiện bởi: ' . $this->actor->name);
        }

        $mailMessage->line('Cảm ơn vì đã sử dụng TaskPilot.');

        return $mailMessage;
    }

    public function toArray($notifiable): array
    {
        return [
            'event_type' => $this->eventType,
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'actor_id' => $this->actor?->id,
            'actor_name' => $this->actor?->name,
            'actor_avatar' => $this->actor?->avatar,
            'data' => $this->data ?? [],
        ];
    }
}
