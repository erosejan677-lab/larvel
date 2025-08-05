<?php

return [
    'allowed_headers' => ['Content-Type', 'X-Requested-With', 'Authorization', 'Origin', 'Accept'],
    'paths' => ['api/*'],
    'allowed_methods' => ['*'],
    'allowed_origins' =>
        ['https://closyyyy.com',
        'http://localhost:5182'],
    'allowed_origins_patterns' => [],
    'exposed_headers' => [],
    'max_age' => 0,
    'supports_credentials' => false,


];
