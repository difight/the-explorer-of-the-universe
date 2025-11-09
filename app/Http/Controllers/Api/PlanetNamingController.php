<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Discovery;
use App\Models\Planet;
use App\Services\NameModerationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PlanetNamingController extends Controller
{
    private NameModerationService $moderationService;

    public function __construct(NameModerationService $moderationService)
    {
        $this->moderationService = $moderationService;
    }

    public function namePlanet(Request $request, Planet $planet): JsonResponse
    {
        $user = $request->user();

        if (!$planet->getCanBeNamedByUser($user)) {
            return response()->json([
                'error' => 'Вы не можете назвать эту планету'
            ], 403);
        }

        $validation = $this->moderationService->validateName($request->name);

        if (!$validation['is_valid']) {
            return response()->json([
                'error' => 'Некорректное имя',
                'errors' => $validation['errors'],
                'suggestion' => $this->moderationService->suggestAlternative($request->name)
            ], 422);
        }

        // Проверка на уникальность
        if ($this->isNameAlreadyTaken($request->name)) {
            return response()->json([
                'error' => 'Это имя уже занято',
                'suggestion' => $this->moderationService->suggestAlternative($request->name)
            ], 422);
        }

        $discovery = $planet->getUserDiscovery($user);
        $discovery->update([
            'custom_name' => $request->name,
            'status' => 'pending' // Отправлено на модерацию
        ]);

        return response()->json([
            'message' => 'Имя планеты отправлено на модерацию. Ожидайте проверки.',
            'data' => [
                'planet_id' => $planet->id,
                'proposed_name' => $request->name,
                'status' => 'pending',
                'estimated_moderation_time' => '1-24 часа'
            ]
        ]);
    }

    private function isNameAlreadyTaken(string $name): bool
    {
        return Discovery::where('custom_name', $name)
            ->where('status', 'approved')
            ->exists();
    }


    public function getUserNameablePlanets(Request $request): JsonResponse
    {
        $user = $request->user();

        $discoveries = Discovery::with(['planet.starSystem'])
            ->where('user_id', $user->id)
            ->whereNull('custom_name')
            ->where('status', 'pending')
            ->get();

        return response()->json([
            'data' => $discoveries->map(function($discovery) {
                return [
                    'discovery_id' => $discovery->id,
                    'planet' => [
                        'id' => $discovery->planet->id,
                        'tech_name' => $discovery->planet->tech_name,
                        'type' => $discovery->planet->type,
                        'system' => $discovery->planet->starSystem->name,
                    ],
                    'discovered_at' => $discovery->discovered_at,
                ];
            })
        ]);
    }

    public function getUserNamedPlanets(Request $request): JsonResponse
    {
        $user = $request->user();

        $discoveries = Discovery::with(['planet.starSystem'])
            ->where('user_id', $user->id)
            ->whereNotNull('custom_name')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json([
            'data' => $discoveries->map(function($discovery) {
                return [
                    'discovery_id' => $discovery->id,
                    'planet' => [
                        'id' => $discovery->planet->id,
                        'display_name' => $discovery->planet->display_name,
                        'custom_name' => $discovery->custom_name,
                        'status' => $discovery->status,
                        'type' => $discovery->planet->type,
                        'system' => $discovery->planet->starSystem->name,
                    ],
                    'named_at' => $discovery->updated_at,
                ];
            })
        ]);
    }

}
