<?php

// config for Albet/SanctumRefresh
return [
    /**
     * Set the fallback expiration time of both tokens
     * Time in minutes.
     */
    'expiration' => [
        // set the fallback of access token expiration
        'access_token' => 60, // 60 minutes,
        // set the fallback of refresh token expiration
        'refresh_token' => 1440, // 24 hours
    ],
];
