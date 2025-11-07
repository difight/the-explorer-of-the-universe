<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\TravelTimeService;
use App\Services\PlanetGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SatelliteController extends Controller
{
    public function __construct(
        private TravelTimeService $travelTimeService
    ) {}
    public function show(): JsonResponse
    {
        $satellite = auth()->user()->satellite->load('user');

        return response()->json([
            'data' => [
                'id' => $satellite->id,
                'name' => $satellite->name,
                'position' => [
                    'x' => $satellite->current_x,
                    'y' => $satellite->current_y,
                    'z' => $satellite->current_z,
                ],
                'target' => $satellite->target_x ? [
                    'x' => $satellite->target_x,
                    'y' => $satellite->target_y,
                    'z' => $satellite->target_z,
                ] : null,
                'arrival_time' => $satellite->arrival_time,
                'status' => $satellite->status,
                'energy' => $satellite->energy,
                'integrity' => $satellite->integrity,
                'travel_progress' => $satellite->travel_progress,
                'malfunctions' => $satellite->malfunctions ?? [],
            ]
        ]);
    }

    public function travel(Request $request): JsonResponse
    {
        $request->validate([
            'direction_x' => 'required|integer|between:-1,1',
            'direction_y' => 'required|integer|between:-1,1',
            'direction_z' => 'required|integer|between:-1,1',
        ]);

        $satellite = auth()->user()->satellite;

        if ($satellite->isTraveling()) {
            return response()->json([
                'error' => 'Спутник уже в полете'
            ], 400);
        }

        if ($satellite->energy < 20) {
            return response()->json([
                'error' => 'Недостаточно энергии для маневра'
            ], 400);
        }

        $targetX = $satellite->current_x + $request->direction_x;
        $targetY = $satellite->current_y + $request->direction_y;
        $targetZ = $satellite->current_z + $request->direction_z;

        // Получаем целевую систему (или создаем запись)
        $targetSystem = \App\Models\StarSystem::findOrCreateAt($targetX, $targetY, $targetZ);

        // Генерируем систему если нужно, чтобы узнать тип звезды
        $planetGenerator = new PlanetGeneratorService();
        $planetGenerator->generateForSystem($targetSystem);

        // Используем сервис для расчета времени полета
        $travelTimeHours = $this->travelTimeService->calculateForStarType($targetSystem->star_type);
        $arrivalTime = now()->addHours($travelTimeHours);

        $satellite->update([
            'target_x' => $targetX,
            'target_y' => $targetY,
            'target_z' => $targetZ,
            'arrival_time' => $arrivalTime,
            'status' => 'traveling',
            'energy' => $satellite->energy - 20
        ]);

        return response()->json([
            'message' => 'Спутник отправлен в новую систему',
            'data' => [
                'target_system' => $targetSystem->name,
                'star_type' => $targetSystem->star_type,
                'travel_time_hours' => $travelTimeHours,
                'arrival_time' => $arrivalTime,
                'remaining_energy' => $satellite->energy
            ]
        ]);
    }

    public function repair(Request $request): JsonResponse
    {
        $satellite = auth()->user()->satellite;

        // Базовая логика ремонта - восстанавливаем 30% целостности за 10 энергии
        if ($satellite->energy < 10) {
            return response()->json([
                'error' => 'Недостаточно энергии для ремонта'
            ], 400);
        }

        $repairAmount = min(30, 100 - $satellite->integrity);

        $satellite->update([
            'integrity' => $satellite->integrity + $repairAmount,
            'energy' => $satellite->energy - 10,
            'malfunctions' => [] // Очищаем поломки после ремонта
        ]);

        return response()->json([
            'message' => 'Ремонт выполнен успешно',
            'data' => [
                'integrity_increased' => $repairAmount,
                'new_integrity' => $satellite->integrity,
                'energy_used' => 10,
                'remaining_energy' => $satellite->energy
            ]
        ]);
    }

    public function stats(): JsonResponse
    {
        $user = auth()->user();
        $satellite = $user->satellite;

        $stats = [
            'user' => [
                'name' => $user->name,
                'discoveries_count' => $user->discoveries()->count(),
                'systems_visited' => $this->getUniqueSystemsCount($user),
            ],
            'satellite' => [
                'name' => $satellite->name,
                'total_travel_distance' => $this->calculateTravelDistance($satellite),
                'status' => $satellite->status,
            ],
            'achievements' => [
                'total' => $user->achievements()->count(),
                'recent' => $user->achievements()->latest()->take(3)->get()
            ]
        ];

        return response()->json(['data' => $stats]);
    }

    private function getUniqueSystemsCount($user): int
    {
        return $user->discoveries()
            ->with('planet.starSystem')
            ->get()
            ->groupBy('planet.starSystem.id')
            ->count();
    }

    private function calculateTravelDistance($satellite): int
    {
        // Простая метрика - сумма абсолютных значений координат
        return abs($satellite->current_x) + abs($satellite->current_y) + abs($satellite->current_z);
    }
}
