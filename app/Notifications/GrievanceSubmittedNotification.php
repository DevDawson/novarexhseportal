<?php

namespace App\Notifications;

use App\Models\Grievance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GrievanceSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Grievance $grievance) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $category = \App\Models\Grievance::CATEGORY_LABELS[$this->grievance->category] ?? $this->grievance->category;

        return (new MailMessage)
            ->subject("[NOVAREX] New Grievance Submitted — {$this->grievance->reference}")
            ->greeting('New Grievance Received')
            ->line("**Reference:** {$this->grievance->reference}")
            ->line("**Category:** {$category}")
            ->line("**Severity:** " . ucfirst($this->grievance->severity))
            ->line("**Received:** {$this->grievance->received_date?->format('d M Y')}")
            ->line("**Description:** {$this->grievance->description}")
            ->action('View Grievance', url('/admin/grievances/' . $this->grievance->id . '/edit'))
            ->salutation('NOVAREX ESG System | support@novarex.co.tz');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Grievance: ' . $this->grievance->reference,
            'body'  => ucfirst($this->grievance->severity) . ' severity — '
                       . (\App\Models\Grievance::CATEGORY_LABELS[$this->grievance->category] ?? $this->grievance->category),
            'url'   => '/admin/grievances/' . $this->grievance->id . '/edit',
            'icon'  => 'heroicon-o-megaphone',
            'color' => $this->grievance->severity === 'high' ? 'danger' : 'warning',
        ];
    }
}
