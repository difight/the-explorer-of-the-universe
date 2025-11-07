<?php

namespace App\Console\Commands;

use App\Models\Satellite;
use App\Jobs\ProcessSatelliteArrival;
use Illuminate\Console\Command;

class ProcessTravel extends Command
{
    protected $signature = 'satellites:process-travel';
    protected $description = 'Dispatch jobs for satellite arrivals';

    public function handle()
    {
        $this->info('Dispatching arrival jobs...');

        $arrivedSatellites = Satellite::where('status', 'traveling')
            ->where('arrival_time', '<=', now())
            ->get();

        $this->info("Found {$arrivedSatellites->count()} satellites to process");

        foreach ($arrivedSatellites as $satellite) {
            ProcessSatelliteArrival::dispatch($satellite);
            $this->info("Dispatched job for satellite {$satellite->id}");
        }

        // Также можно диспатчить задачи для спутников, которые скоро прибудут
        $soonToArrive = Satellite::where('status', 'traveling')
            ->where('arrival_time', '<=', now()->addMinutes(5))
            ->where('arrival_time', '>', now())
            ->get();

        foreach ($soonToArrive as $satellite) {
            // Диспатчим с задержкой
            $delaySeconds = $satellite->arrival_time->diffInSeconds(now());
            ProcessSatelliteArrival::dispatch($satellite)->delay(now()->addSeconds($delaySeconds));
            $this->info("Dispatched delayed job for satellite {$satellite->id} (in {$delaySeconds} seconds)");
        }

        return Command::SUCCESS;
    }
}
