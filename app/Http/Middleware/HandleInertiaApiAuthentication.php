<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HandleInertiaApiAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Для Inertia запросов проверяем авторизацию через веб-гард
        if ($request->header('X-Inertia')) {
            if (!Auth::check()) {
                // Для API-ориентированного подхода возвращаем JSON ответ вместо редиректа
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Unauthenticated.'], 401);
                }

                return redirect()->route('home');
            }

            return $next($request);
        }

        // Для обычных запросов проверяем авторизацию стандартным способом
        if (!Auth::check()) {
            return redirect()->route('home');
        }

        return $next($request);
    }
}
