<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LogRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Логируем в стандартный лог Laravel
        Log::info('LogRequests middleware called', [
            'url' => $request->url(),
            'method' => $request->method(),
            'headers' => $request->headers->all(),
            'cookies' => $request->cookies->all(),
        ]);

        // Логируем в отдельный файл для удобства
        $logMessage = sprintf(
            "[%s] %s %s\nHeaders: %s\nCookies: %s\n\n",
            now()->toDateTimeString(),
            $request->method(),
            $request->url(),
            json_encode($request->headers->all(), JSON_PRETTY_PRINT),
            json_encode($request->cookies->all(), JSON_PRETTY_PRINT)
        );

        file_put_contents(
            storage_path('logs/waytobackend.log'),
            $logMessage,
            FILE_APPEND | LOCK_EX
        );

        return $next($request);
    }
}
