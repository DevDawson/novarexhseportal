<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class IncidentReportedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Incident $incident) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type     = ucwords(str_replace('_', ' ', $this->incident->incident_type));
        $project  = $this->incident->project?->title ?? 'Company-wide';
        $reporter = $this->incident->reportedBy?->name ?? 'Unknown';

        return (new MailMessage)
            ->subject("[NOVAREX] New Incident Reported — {$type}")
            ->greeting('New Incident Report')
            ->line("A new **{$type}** incident has been reported.")
            ->line("**Date:** {$this->incident->incident_date?->format('d M Y')}")
            ->line("**Project:** {$project}")
            ->line("**Reported By:** {$reporter}")
            ->line("**Description:** {$this->incident->description}")
            ->action('View Incident', url('/admin/incidents/' . $this->incident->id . '/edit'))
            ->salutation('NOVAREX HSE System | support@novarex.co.tz');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title'   => 'New Incident Reported',
            'body'    => ucwords(str_replace('_', ' ', $this->incident->incident_type))
                         . ' on ' . $this->incident->incident_date?->format('d M Y')
                         . ($this->incident->project ? ' — ' . $this->incident->project->title : ''),
            'url'     => '/admin/incidents/' . $this->incident->id . '/edit',
            'icon'    => 'heroicon-o-exclamation-triangle',
            'color'   => 'danger',
        ];
    }
}
