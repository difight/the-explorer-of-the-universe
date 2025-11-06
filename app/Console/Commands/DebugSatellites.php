<?php

namespace App\Console\Commands;

use App\Models\Satellite;
use Illuminate\Console\Command;

class DebugSatellites extends Command
{
    protected $signature = 'debug:satellites';
    protected $description = 'Debug satellite data';

    public function handle()
    {
        $this->info('=== Satellite Debug Information ===');

        $allSatellites = Satellite::with('user')->get();
        $this->info("Total satellites: {$allSatellites->count()}");

        foreach ($allSatellites as $satellite) {
            $this->line("ID: {$satellite->id} | User: {$satellite->user->name} | Status: {$satellite->status}");
            $this->line("Position: [{$satellite->current_x},{$satellite->current_y},{$satellite->current_z}]");
            $this->line("Target: [{$satellite->target_x},{$satellite->target_y},{$satellite->target_z}]");
            $this->line("Arrival: {$satellite->arrival_time}");
            $this->line("Energy: {$satellite->energy}% | Integrity: {$satellite->integrity}%");
            $this->line('---');
        }

        $traveling = Satellite::where('status', 'traveling')->count();
        $this->info("Traveling satellites: {$traveling}");

        $arrived = Satellite::where('status', 'traveling')
            ->where('arrival_time', '<=', now())
            ->count();
        $this->info("Arrived satellites (ready to process): {$arrived}");

        return Command::SUCCESS;
    }
}
