<?php

namespace App\Console\Commands;

use App\Models\Satellite;
use App\Models\Discovery;
use App\Services\PlanetGeneratorService;
use App\Services\AchievementService;
use Illuminate\Console\Command;

class ProcessTravel extends Command
{
    protected $signature = 'satellites:process-travel';
    protected $description = 'Process satellite arrivals and generate systems';

    public function handle()
    {
        $this->info('Starting travel processing...');

        $arrivedSatellites = Satellite::where('status', 'traveling')
            ->where('arrival_time', '<=', now())
            ->with('user')
            ->get();

        $this->info("Found {$arrivedSatellites->count()} satellites to process");

        if ($arrivedSatellites->isEmpty()) {
            $this->info('No satellites arrived yet.');
            return Command::SUCCESS;
        }

        $planetGenerator = new PlanetGeneratorService();
        $achievementService = new AchievementService();

        foreach ($arrivedSatellites as $satellite) {
            $this->info("Processing satellite {$satellite->id} for user {$satellite->user->name}...");

            // Обновляем позицию спутника
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

            // Генерируем систему если нужно
            $currentSystem = $satellite->currentSystem;
            $planetGenerator->generateForSystem($currentSystem);

            // Записываем открытия
            $this->recordDiscoveries($satellite, $currentSystem);

            // Проверяем достижения
            $achievementService->checkPlanetTypeAchievements($satellite->user);

            $this->info("Satellite {$satellite->id} arrived at {$currentSystem->name}");
        }

        $this->info("Successfully processed {$arrivedSatellites->count()} arrivals");
        return Command::SUCCESS;
    }

    private function recordDiscoveries(Satellite $satellite, $system): void
    {
        foreach ($system->planets as $planet) {
            // Проверяем, не открыта ли уже планета
            $existingDiscovery = Discovery::where('planet_id', $planet->id)
                ->where('user_id', $satellite->user_id)
                ->first();

            if (!$existingDiscovery) {
                Discovery::create([
                    'user_id' => $satellite->user_id,
                    'planet_id' => $planet->id,
                    'discovered_at' => now(),
                    'status' => 'pending'
                ]);

                // Если нашли жизнь - выдаем специальное достижение
                if ($planet->has_life) {
                    $achievementService = new AchievementService();
                    $achievementService->checkSpecialAchievements(
                        $satellite->user,
                        'found_life',
                        ['planet_id' => $planet->id, 'system' => $system->name]
                    );
                }
            }
        }
    }
}
