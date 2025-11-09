<?php

namespace App\Jobs;

use App\Models\Satellite;
use App\Services\PlanetGeneratorService;
use App\Services\AchievementService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessSatelliteArrival implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private PlanetGeneratorService $planetGeneratorService;
    private AchievementService $achievementService;

    public function __construct(
        public Satellite $satellite
    ) {
        $this->planetGeneratorService = app(PlanetGeneratorService::class);
        $this->achievementService = app(AchievementService::class);
    }

    public function handle(): void
    {
        // Проверяем что спутник все еще в полете и время пришло
        if (!$this->satellite->hasArrived()) {
            $this->release(60); // Повторим через минуту
            return;
        }

        \Illuminate\Support\Facades\DB::transaction(function () {
            $satellite = $this->satellite->fresh(); // Обновляем данные

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
            $this->planetGeneratorService->generateForSystem($currentSystem);

            // Записываем открытия
            $this->recordDiscoveries($satellite, $currentSystem);

            // Проверяем достижения
            $this->achievementService->checkAllAchievements($satellite->user);
        });
    }

    private function recordDiscoveries(Satellite $satellite, $system): void
    {
        foreach ($system->planets as $planet) {
            $existingDiscovery = \App\Models\Discovery::where('planet_id', $planet->id)
                ->where('user_id', $satellite->user_id)
                ->first();

            if (!$existingDiscovery) {
                \App\Models\Discovery::create([
                    'user_id' => $satellite->user_id,
                    'planet_id' => $planet->id,
                    'discovered_at' => now(),
                    'status' => 'pending'
                ]);

                if ($planet->has_life) {
                    $this->achievementService->checkSpecialAchievements(
                        $satellite->user,
                        'found_life',
                        ['planet_id' => $planet->id, 'system' => $system->name]
                    );
                }
            }
        }
    }
}
