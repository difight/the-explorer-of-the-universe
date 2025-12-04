<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoginResponse implements LoginResponseContract
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
                'user' => auth()->guard(config('fortify.guard'))->user(),
                'message' => 'Login successful'
            ]);
        }

        // For regular requests, redirect to home
        return redirect()->intended(config('fortify.home'));
    }
}
