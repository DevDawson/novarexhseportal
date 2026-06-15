<?php

namespace App\Observers;

use App\Models\LeaveRequest;
use App\Models\User;
use App\Notifications\LeaveRequestStatusChangedNotification;
use App\Notifications\LeaveRequestSubmittedNotification;

class LeaveRequestObserver
{
    public function created(LeaveRequest $leaveRequest): void
    {
        // Notify HR Director and MD about new leave request.
        $recipients = User::role(['md', 'hr_director'])->get();

        foreach ($recipients as $user) {
            $user->notify(new LeaveRequestSubmittedNotification($leaveRequest));
        }
    }

    public function updated(LeaveRequest $leaveRequest): void
    {
        // When status changes to approved or rejected, notify the staff member.
        if (! $leaveRequest->wasChanged('status')) {
            return;
        }

        if (! in_array($leaveRequest->status, ['approved', 'rejected'])) {
            return;
        }

        $staffUser = $leaveRequest->staff?->user;

        if ($staffUser) {
            $staffUser->notify(new LeaveRequestStatusChangedNotification($leaveRequest));
        }
    }
}
