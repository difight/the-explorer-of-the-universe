<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StarSystem;
use App\Services\PlanetGeneratorService;
use Illuminate\Http\JsonResponse;
use App\Services\TravelTimeService;

class SystemController extends Controller
{
    public function __construct(
        private TravelTimeService $travelTimeService
    ) {}
    public function current(): JsonResponse
    {
        $satellite = auth()->user()->satellite;
        $system = $satellite->currentSystem;

        $planetGenerator = new PlanetGeneratorService();
        $planetGenerator->generateForSystem($system);

        $system->load(['planets.discoveries' => function($query) {
            $query->where('user_id', auth()->id())->orWhere('status', 'approved');
        }]);

        return response()->json([
            'data' => $this->formatSystemData($system, true)
        ]);
    }

    public function show(int $x, int $y, int $z): JsonResponse
    {
        $system = StarSystem::findOrCreateAt($x, $y, $z);

        $planetGenerator = new PlanetGeneratorService();
        $planetGenerator->generateForSystem($system);

        $system->load(['planets.discoveries' => function($query) {
            $query->where('user_id', auth()->id())->orWhere('status', 'approved');
        }]);

        return response()->json([
            'data' => $this->formatSystemData($system, false)
        ]);
    }

    public function planets(int $x, int $y, int $z): JsonResponse
    {
        $system = StarSystem::findOrCreateAt($x, $y, $z);

        $planetGenerator = new PlanetGeneratorService();
        $planetGenerator->generateForSystem($system);

        $planets = $system->planets()
            ->with(['discoveries' => function($query) {
                $query->where('user_id', auth()->id())->orWhere('status', 'approved');
            }])
            ->get();

        return response()->json([
            'data' => $planets->map(function($planet) {
                return [
                    'id' => $planet->id,
                    'tech_name' => $planet->tech_name,
                    'display_name' => $planet->display_name,
                    'type' => $planet->type,
                    'has_life' => $planet->has_life,
                    'size' => $planet->size,
                    'orbit_distance' => $planet->orbit_distance,
                    'temperature' => $planet->temperature,
                    'special_features' => $planet->special_features,
                    'is_discovered_by_me' => $planet->isDiscoveredBy(auth()->user()),
                    'discovery_status' => $planet->discovery?->status,
                ];
            })
        ]);
    }

    private function formatSystemData(StarSystem $system, bool $includeNeighbors): array
    {
        $data = [
            'id' => $system->id,
            'name' => $system->name,
            'coordinates' => $system->coordinates,
            'star_type' => $system->star_type,
            'star_mass' => $system->star_mass,
            'is_start_system' => $system->is_start_system,
            'planets' => $system->planets->map(function($planet) {
                return [
                    'id' => $planet->id,
                    'tech_name' => $planet->tech_name,
                    'display_name' => $planet->display_name,
                    'type' => $planet->type,
                    'has_life' => $planet->has_life,
                    'size' => $planet->size,
                    'orbit_distance' => $planet->orbit_distance,
                    'temperature' => $planet->temperature,
                    'special_features' => $planet->special_features,
                    'is_discovered_by_me' => $planet->isDiscoveredBy(auth()->user()),
                    'can_name' => $planet->discovery && $planet->discovery->user_id === auth()->id() && !$planet->discovery->custom_name,
                ];
            })
        ];

        if ($includeNeighbors) {
            $data['neighboring_systems'] = $this->getNeighboringSystems($system);
        }

        return $data;
    }

    private function getNeighboringSystems(StarSystem $system): array
    {
        $directions = [
            ['x' => 1, 'y' => 0, 'z' => 0, 'name' => 'восток'],
            ['x' => -1, 'y' => 0, 'z' => 0, 'name' => 'запад'],
            ['x' => 0, 'y' => 1, 'z' => 0, 'name' => 'север'],
            ['x' => 0, 'y' => -1, 'z' => 0, 'name' => 'юг'],
            ['x' => 0, 'y' => 0, 'z' => 1, 'name' => 'верх'],
            ['x' => 0, 'y' => 0, 'z' => -1, 'name' => 'низ'],
        ];

        $neighbors = [];
        foreach ($directions as $direction) {
            $neighbor = StarSystem::findOrCreateAt(
                $system->coord_x + $direction['x'],
                $system->coord_y + $direction['y'],
                $system->coord_z + $direction['z']
            );

            // Генерируем систему если нужно, чтобы узнать тип звезды
            $planetGenerator = new PlanetGeneratorService();
            $planetGenerator->generateForSystem($neighbor);

            // Используем сервис для расчета времени полета
            $travelTime = $this->travelTimeService->calculateForStarType($neighbor->star_type);

            $neighbors[] = [
                'coordinates' => $neighbor->coordinates,
                'name' => $neighbor->name,
                'direction' => $direction['name'],
                'star_type' => $neighbor->star_type,
                'travel_time_hours' => $travelTime,
                'is_generated' => $neighbor->is_generated,
            ];
        }

        return $neighbors;
    }

}
