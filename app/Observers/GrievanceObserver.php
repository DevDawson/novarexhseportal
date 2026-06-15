<?php

namespace App\Observers;

use App\Models\Grievance;
use App\Models\User;
use App\Notifications\GrievanceSubmittedNotification;

class GrievanceObserver
{
    public function created(Grievance $grievance): void
    {
        // Notify MD and ESG Officers.
        $recipients = User::role(['md', 'esg_officer'])->get();

        foreach ($recipients as $user) {
            $user->notify(new GrievanceSubmittedNotification($grievance));
        }
    }
}
