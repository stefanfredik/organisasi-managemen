<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeactivateExpiredContributions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contributions:deactivate-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate contribution types that have reached their due date';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $today = now()->toDateString();

        $expiredContributions = \App\Models\ContributionType::where('is_active', true)
            ->whereNotNull('due_date')
            ->where('due_date', '<', $today)
            ->get();

        $count = 0;
        foreach ($expiredContributions as $contribution) {
            $contribution->update(['is_active' => false]);
            $count++;
            $this->info("Deactivated: {$contribution->name} (Due: {$contribution->due_date})");
        }

        if ($count > 0) {
            $this->info("Total deactivated: {$count} contribution type(s)");
        } else {
            $this->info('No expired contribution types found.');
        }

        return 0;
    }
}
