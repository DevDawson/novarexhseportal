<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestStatusChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly LeaveRequest $leaveRequest) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $status = ucfirst($this->leaveRequest->status);
        $color  = $this->leaveRequest->status === 'approved' ? 'success' : 'danger';

        return (new MailMessage)
            ->subject("[NOVAREX] Leave Request {$status}")
            ->greeting("Your Leave Request Has Been {$status}")
            ->line("**Leave Type:** {$this->leaveRequest->leaveType?->name}")
            ->line("**From:** {$this->leaveRequest->start_date?->format('d M Y')} **To:** {$this->leaveRequest->end_date?->format('d M Y')}")
            ->line("**Decision By:** {$this->leaveRequest->approvedBy?->name}")
            ->action('View Request', url('/admin/leave-requests/' . $this->leaveRequest->id . '/edit'))
            ->salutation('NOVAREX HR System | support@novarex.co.tz');
    }

    public function toArray(object $notifiable): array
    {
        $status = ucfirst($this->leaveRequest->status);
        return [
            'title' => 'Leave Request ' . $status,
            'body'  => $this->leaveRequest->days_requested . ' day(s) from '
                       . $this->leaveRequest->start_date?->format('d M Y') . ' — ' . $status,
            'url'   => '/admin/leave-requests/' . $this->leaveRequest->id . '/edit',
            'icon'  => $this->leaveRequest->status === 'approved'
                       ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle',
            'color' => $this->leaveRequest->status === 'approved' ? 'success' : 'danger',
        ];
    }
}
