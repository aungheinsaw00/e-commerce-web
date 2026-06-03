<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Read notification retention (days)
    |--------------------------------------------------------------------------
    |
    | Notifications marked read longer than this are deleted by the prune
    | command. Set to 0 to disable pruning.
    |
    */

    'read_retention_days' => (int) env('NOTIFICATION_READ_RETENTION_DAYS', 10),

];
