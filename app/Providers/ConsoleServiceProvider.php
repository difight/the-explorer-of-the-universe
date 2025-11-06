<?php

namespace App\Providers;

use App\Console\Commands\ProcessTravel;
use Illuminate\Support\ServiceProvider;
use App\Console\Commands\DebugSatellites;

class ConsoleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->commands([
            ProcessTravel::class,
            DebugSatellites::class,
        ]);
    }
}
