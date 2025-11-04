<?php

namespace App\Console\Commands;

use App\Models\Satellite;
use Illuminate\Console\Command;

class ProcessTravel extends Command
{
    protected $signature = 'satellites:process-travel';
    protected $description = 'Process satellite arrivals';

    public function handle()
    {
        $arrivedSatellites = Satellite::where('status', 'traveling')
            ->where('arrival_time', '<=', now())
            ->get();

        foreach ($arrivedSatellites as $satellite) {
            $satellite->update([
                'current_x' => $satellite->target_x,
                'current_y' => $satellite->target_y,
                'current_z' => $satellite->target_z,
                'target_x' => null,
                'target_y' => null,
                'target_z' => null,
                'arrival_time' => null,
                'status' => 'idle'
            ]);

            $this->info("Satellite {$satellite->id} arrived at destination");
        }

        $this->info("Processed {$arrivedSatellites->count()} satellites");
    }
}
