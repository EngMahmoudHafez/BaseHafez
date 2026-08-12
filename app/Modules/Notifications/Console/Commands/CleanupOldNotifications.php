<?php

namespace App\Modules\Notifications\Console\Commands;

use App\Modules\Notifications\Http\Services\Dashboard\NotificationService;
use Illuminate\Console\Command;

class CleanupOldNotifications extends Command
{
    protected $signature = 'notifications:cleanup {--days=30 : Number of days to keep notifications}';

    protected $description = 'Delete old notifications older than specified days';

    public function handle(NotificationService $notificationService): int
    {
        $days = (int) $this->option('days');
        $count = $notificationService->deleteOldNotifications($days);

        $this->info("Deleted {$count} old notifications.");

        return Command::SUCCESS;
    }
}
