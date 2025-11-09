<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Discovery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;

class ModerationController extends Controller
{
    public static function middleware(): array
    {
        return ['admin'];
    }

    public function getPendingNames(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 20);
        
        $pendingNames = Discovery::with(['user', 'planet.starSystem'])
            ->needsModeration()
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'data' => $pendingNames->map(function($discovery) {
                return [
                    'discovery_id' => $discovery->id,
                    'user' => [
                        'id' => $discovery->user->id,
                        'name' => $discovery->user->name,
                    ],
                    'planet' => [
                        'id' => $discovery->planet->id,
                        'tech_name' => $discovery->planet->tech_name,
                        'type' => $discovery->planet->type,
                        'system' => $discovery->planet->starSystem->name,
                    ],
                    'proposed_name' => $discovery->custom_name,
                    'submitted_at' => $discovery->updated_at,
                ];
            }),
            'meta' => [
                'total' => $pendingNames->total(),
                'current_page' => $pendingNames->currentPage(),
                'per_page' => $pendingNames->perPage(),
            ]
        ]);
    }

    public function approveName(Request $request, Discovery $discovery): JsonResponse
    {
        $discovery->update([
            'status' => 'approved',
            'moderated_at' => now(),
            'moderated_by' => $request->user()->id,
        ]);

        // Можно добавить уведомление пользователю
        // Notification::send($discovery->user, new PlanetNameApproved($discovery));

        return response()->json([
            'message' => 'Имя планеты утверждено',
            'data' => [
                'planet_id' => $discovery->planet_id,
                'approved_name' => $discovery->custom_name,
                'user_id' => $discovery->user_id,
            ]
        ]);
    }

    public function rejectName(Request $request, Discovery $discovery): JsonResponse
    {
        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $discovery->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
            'moderated_at' => now(),
            'moderated_by' => $request->user()->id,
            'custom_name' => null, // Очищаем предложенное имя
        ]);

        // Можно добавить уведомление пользователю
        // Notification::send($discovery->user, new PlanetNameRejected($discovery));

        return response()->json([
            'message' => 'Имя планеты отклонено',
            'data' => [
                'planet_id' => $discovery->planet_id,
                'reason' => $request->reason,
                'user_id' => $discovery->user_id,
            ]
        ]);
    }

    public function getModerationStats(): JsonResponse
    {
        $stats = [
            'pending' => Discovery::needsModeration()->count(),
            'approved_today' => Discovery::approved()->whereDate('moderated_at', today())->count(),
            'rejected_today' => Discovery::rejected()->whereDate('moderated_at', today())->count(),
            'total_approved' => Discovery::approved()->count(),
            'total_rejected' => Discovery::rejected()->count(),
        ];

        return response()->json(['data' => $stats]);
    }
}