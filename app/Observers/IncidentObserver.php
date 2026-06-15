<?php

namespace App\Observers;

use App\Models\Incident;
use App\Models\User;
use App\Notifications\IncidentReportedNotification;

class IncidentObserver
{
    public function created(Incident $incident): void
    {
        // Notify all MD and HSE Staff users.
        $recipients = User::role(['md', 'hse_staff'])->get();

        foreach ($recipients as $user) {
            $user->notify(new IncidentReportedNotification($incident));
        }
    }
}
