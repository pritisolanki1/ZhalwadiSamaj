<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Zip Daily Challenge Reminder
    |--------------------------------------------------------------------------
    |
    | Settings for the daily Zip Puzzle challenge reminder notification.
    |
    | daily_reminder_time: 24-hour "H:i" string using the application's
    | server timezone (currently UTC). The scheduler runs the
    | "zip:send-daily-reminders" command at this time every day.
    |
    | Example: "10:00" schedules the reminder for 10:00 server (UTC) time.
    |
    */

    'daily_reminder_time' => env('ZIP_DAILY_REMINDER_TIME', '05:00'),
];