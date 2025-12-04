<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutResponse implements LogoutResponseContract
{
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        // For API requests, return JSON response
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Logout successful'
            ]);
        }

        // For regular requests, redirect to home
        return redirect('/');
    }
}
