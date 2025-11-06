<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class AchievementController extends Controller
{
    public function index(): JsonResponse
    {
        $achievements = auth()->user()
            ->achievements()
            ->orderBy('achieved_at', 'desc')
            ->get();

        return response()->json([
            'data' => $achievements->map(function($achievement) {
                return [
                    'name' => $achievement->name,
                    'icon' => $achievement->icon,
                    'type' => $achievement->type,
                    'achieved_at' => $achievement->achieved_at,
                    'metadata' => $achievement->metadata,
                ];
            })
        ]);
    }

    public function leaderboard(): JsonResponse
    {
        $topExplorers = User::withCount('discoveries')
            ->orderBy('discoveries_count', 'desc')
            ->limit(20)
            ->get()
            ->map(function($user) {
                return [
                    'user_name' => $user->name,
                    'discoveries_count' => $user->discoveries_count,
                    'joined_at' => $user->created_at,
                ];
            });

        return response()->json(['data' => $topExplorers]);
    }
}
