<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'], // ✅ Needed for API and Sanctum

    'allowed_methods' => ['*'], // Allows all HTTP methods (GET, POST, etc.)

    'allowed_origins' => ['https://frontend-e.onrender.com', 'http://localhost:3000'], // ✅ Your Next.js frontend

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Allow all headers

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true, // ✅ Required for Sanctum sessions/cookies
];

