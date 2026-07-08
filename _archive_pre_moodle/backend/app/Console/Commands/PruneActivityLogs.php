<?php

namespace App\Console\Commands;

use App\Models\UsageLog;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Data-retention: delete system activity logs older than the retention window.
 * Supports the thesis privacy requirement (default 90-day retention for
 * transient usage data). Schedule in routes/console.php or run manually.
 */
class PruneActivityLogs extends Command
{
    protected $signature = 'acculearn:prune-logs {--days=90 : Retention window in days}';

    protected $description = 'Delete usage/activity logs older than the retention window';

    public function handle(): int
    {
        $days   = max(1, (int) $this->option('days'));
        $cutoff = Carbon::now()->subDays($days);

        $deleted = UsageLog::where('created_at', '<', $cutoff)->delete();

        $this->info("Pruned {$deleted} activity log(s) older than {$days} day(s) (before {$cutoff->toDateString()}).");

        return self::SUCCESS;
    }
}
