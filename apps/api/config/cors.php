<?php

return [
    'paths' => ['api/*'],
    'allowed_origins' => [env('FRONTEND_URL', 'http://localhost:3000')],
];