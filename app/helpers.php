<?php

use App\ActivityLog;

if (! function_exists('logActivity')) {
    function logActivity($activity)
    {
        ActivityLog::create([
            'user_id'    => auth()->id(),
            'activity'   => $activity,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
