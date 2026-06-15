<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeaveRequestSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly LeaveRequest $leaveRequest) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $staff = $this->leaveRequest->staff;
        $name  = $staff ? $staff->first_name . ' ' . $staff->last_name : 'Unknown';

        return (new MailMessage)
            ->subject("[NOVAREX] Leave Request — {$name}")
            ->greeting('New Leave Request Submitted')
            ->line("**{$name}** has submitted a leave request.")
            ->line("**Leave Type:** {$this->leaveRequest->leaveType?->name}")
            ->line("**From:** {$this->leaveRequest->start_date?->format('d M Y')} **To:** {$this->leaveRequest->end_date?->format('d M Y')}")
            ->line("**Days:** {$this->leaveRequest->days_requested}")
            ->line("**Reason:** " . ($this->leaveRequest->reason ?? 'Not specified'))
            ->action('Review Request', url('/admin/leave-requests/' . $this->leaveRequest->id . '/edit'))
            ->salutation('NOVAREX HR System | support@novarex.co.tz');
    }

    public function toArray(object $notifiable): array
    {
        $staff = $this->leaveRequest->staff;
        $name  = $staff ? $staff->first_name . ' ' . $staff->last_name : 'Unknown';

        return [
            'title' => 'Leave Request: ' . $name,
            'body'  => ($this->leaveRequest->days_requested ?? '?') . ' day(s) from '
                       . $this->leaveRequest->start_date?->format('d M Y'),
            'url'   => '/admin/leave-requests/' . $this->leaveRequest->id . '/edit',
            'icon'  => 'heroicon-o-calendar-days',
            'color' => 'warning',
        ];
    }
}
