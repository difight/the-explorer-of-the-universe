<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Discovery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DiscoveryController extends Controller
{
    public function index(): JsonResponse
    {
        $discoveries = auth()->user()
            ->discoveries()
            ->with('planet.starSystem')
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $discoveries->map(function($discovery) {
                return [
                    'id' => $discovery->id,
                    'planet' => [
                        'id' => $discovery->planet->id,
                        'tech_name' => $discovery->planet->tech_name,
                        'display_name' => $discovery->planet->display_name,
                        'type' => $discovery->planet->type,
                        'has_life' => $discovery->planet->has_life,
                        'system' => $discovery->planet->starSystem->name,
                    ],
                    'custom_name' => $discovery->custom_name,
                    'status' => $discovery->status,
                    'discovered_at' => $discovery->discovered_at,
                    'can_rename' => !$discovery->custom_name && $discovery->isPending(),
                ];
            }),
            'meta' => [
                'total' => $discoveries->total(),
                'current_page' => $discoveries->currentPage(),
                'last_page' => $discoveries->lastPage(),
            ]
        ]);
    }

    public function namePlanet(Request $request, Discovery $discovery): JsonResponse
    {
        // Проверяем, что это открытие принадлежит пользователю
        if ($discovery->user_id !== auth()->id()) {
            return response()->json(['error' => 'Недостаточно прав'], 403);
        }

        if ($discovery->custom_name) {
            return response()->json(['error' => 'Планета уже имеет имя'], 400);
        }

        $request->validate([
            'name' => 'required|string|min:3|max:30|regex:/^[a-zA-Z0-9\s\-]+$/',
        ]);

        // Простая проверка на мат (можно заменить на более сложную)
        if ($this->containsProfanity($request->name)) {
            return response()->json(['error' => 'Недопустимое имя'], 400);
        }

        $discovery->update([
            'custom_name' => $request->name,
            'status' => 'pending' // Ждет модерации
        ]);

        return response()->json([
            'message' => 'Имя планеты отправлено на модерацию',
            'data' => [
                'planet_id' => $discovery->planet_id,
                'custom_name' => $request->name,
                'status' => 'pending'
            ]
        ]);
    }

    public function hallOfFame(): JsonResponse
    {
        $lifeDiscoveries = Discovery::whereHas('planet', function($query) {
                $query->where('has_life', true);
            })
            ->with(['user', 'planet.starSystem'])
            ->approved()
            ->orderBy('discovered_at')
            ->limit(50)
            ->get();

        return response()->json([
            'data' => $lifeDiscoveries->map(function($discovery) {
                return [
                    'user_name' => $discovery->user->name,
                    'planet_name' => $discovery->planet->display_name,
                    'system_name' => $discovery->planet->starSystem->name,
                    'discovered_at' => $discovery->discovered_at,
                    'planet_type' => $discovery->planet->type,
                ];
            })
        ]);
    }

    private function containsProfanity(string $text): bool
    {
        $profanityWords = [
            'мат', 'слово', 'еще' // Заполнить реальным списком
        ];

        $text = Str::lower($text);

        foreach ($profanityWords as $word) {
            if (Str::contains($text, $word)) {
                return true;
            }
        }

        return false;
    }
}
