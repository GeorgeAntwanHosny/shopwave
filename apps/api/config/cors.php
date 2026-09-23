<?php

return [
    'paths' => ['api/*', 'broadcasting/*'],

    'allowed_methods' => ['*'],

    // Strictly the frontend's own origin — never a wildcard. Read from
    // FRONTEND_URL so this automatically stays correct across native dev,
    // Docker, and any future real deployment without editing this file.
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,
];
