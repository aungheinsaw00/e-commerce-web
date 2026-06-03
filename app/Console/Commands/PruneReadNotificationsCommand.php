<?php

namespace App\Console\Commands;

use App\Services\NotificationService;
use Illuminate\Console\Command;

class PruneReadNotificationsCommand extends Command
{
    protected $signature = 'notifications:prune-read
                            {--days= : Delete read notifications older than this many days}';

    protected $description = 'Delete notifications that have been read for longer than the retention period';

    public function handle(NotificationService $notificationService): int
    {
        $days = $this->option('days') !== null
            ? (int) $this->option('days')
            : (int) config('notifications.read_retention_days');

        if ($days <= 0) {
            $this->info('Notification pruning is disabled (retention days <= 0).');

            return self::SUCCESS;
        }

        $deleted = $notificationService->pruneReadOlderThan($days);

        $this->info("Deleted {$deleted} read notification(s) older than {$days} day(s).");

        return self::SUCCESS;
    }
}
