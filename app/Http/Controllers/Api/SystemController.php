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
        private TravelTimeService $travelTimeService,
        private PlanetGeneratorService $planetGeneratorService
    ) {}
    public function current(): JsonResponse
    {
        $satellite = request()->user()->satellite;
        $system = $satellite->currentSystem;

        $this->planetGeneratorService->generateForSystem($system);

        $system->load(['planets.discoveries' => function($query) {
            $query->where('user_id', request()->user()->id)->orWhere('status', 'approved');
        }]);

        return response()->json([
            'data' => $this->formatSystemData($system, true)
        ]);
    }

    public function show(int $x, int $y, int $z): JsonResponse
    {
        $system = StarSystem::findOrCreateAt($x, $y, $z);

        $this->planetGeneratorService->generateForSystem($system);

        $system->load(['planets.discoveries' => function($query) {
            $query->where('user_id', request()->user()->id)->orWhere('status', 'approved');
        }]);

        return response()->json([
            'data' => $this->formatSystemData($system, false)
        ]);
    }

    public function planets(int $x, int $y, int $z): JsonResponse
    {
        $system = StarSystem::findOrCreateAt($x, $y, $z);

        $this->planetGeneratorService->generateForSystem($system);

        $planets = $system->planets()
            ->with(['discoveries' => function($query) {
                $query->where('user_id', request()->user()->id)->orWhere('status', 'approved');
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
                    'is_discovered_by_me' => $planet->isDiscoveredBy(request()->user()),
                    'discovery_status' => $planet->discovery?->status,
                    'planet_photo' => $planet->getPlanetImageUrl()
                ];
            })
        ]);
    }

    private function formatSystemData(StarSystem $system, bool $includeNeighbors): array
    {
        $user = request()->user();

        $data = [
            'id' => $system->id,
            'name' => $system->name,
            'coordinates' => $system->coordinates,
            'star_type' => $system->star_type,
            'star_mass' => $system->star_mass,
            'is_start_system' => $system->is_start_system,
            'planets' => $system->planets->map(function($planet) use ($user) {
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
                    'image_url' => $planet->getPlanetImageUrl(),
                    'is_discovered_by_me' => $planet->isDiscoveredBy($user),
                    'can_name' => $planet->canBeNamedByUser($user), // Новая информация
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
            $currentDirectionX = $system->coord_x + $direction['x'];
            $currentDirectionY = $system->coord_y + $direction['y'];
            $currentDirectionZ = $system->coord_z + $direction['z'];
            $neighbor = StarSystem::findOrCreateAt(
                $currentDirectionX,
                $currentDirectionY,
                $currentDirectionZ
            );

            // Генерируем систему если нужно, чтобы узнать тип звезды
            $this->planetGeneratorService->generateForSystem($neighbor);

            // Используем сервис для расчета времени полета
            $travelTime = $this->travelTimeService->calculateForStarType($neighbor->star_type);

            $neighbors[] = [
                'coordinates' => [
                    'x' => $currentDirectionX,
                    'y' => $currentDirectionY,
                    'z' => $currentDirectionZ,
                ],
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
