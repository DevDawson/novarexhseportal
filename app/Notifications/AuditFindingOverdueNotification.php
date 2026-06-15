<?php

namespace App\Notifications;

use App\Models\AuditFinding;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuditFindingOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly AuditFinding $finding) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $type = \App\Models\AuditFinding::FINDING_TYPE_LABELS[$this->finding->finding_type] ?? $this->finding->finding_type;
        $ref  = $this->finding->internalAudit?->audit_reference ?? 'N/A';

        return (new MailMessage)
            ->subject("[NOVAREX] Overdue Audit Finding — {$ref}")
            ->greeting('Overdue Audit Finding Alert')
            ->line("An audit finding is past its target closure date.")
            ->line("**Audit Reference:** {$ref}")
            ->line("**Finding Type:** {$type}")
            ->line("**Target Date:** {$this->finding->target_date?->format('d M Y')}")
            ->line("**Description:** {$this->finding->description}")
            ->action('View Finding', url('/admin/internal-audits/' . $this->finding->internal_audit_id . '/edit'))
            ->salutation('NOVAREX Audit System | support@novarex.co.tz');
    }

    public function toArray(object $notifiable): array
    {
        $ref = $this->finding->internalAudit?->audit_reference ?? 'N/A';
        return [
            'title' => 'Overdue Finding: ' . $ref,
            'body'  => 'Target was ' . $this->finding->target_date?->format('d M Y')
                       . ' — ' . ($this->finding->description ?? ''),
            'url'   => '/admin/internal-audits/' . $this->finding->internal_audit_id . '/edit',
            'icon'  => 'heroicon-o-clock',
            'color' => 'danger',
        ];
    }
}
