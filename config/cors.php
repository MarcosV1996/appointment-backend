<?php

return [
    'paths' => ['api/*'], 

    'allowed_methods' => ['*'], 

    'allowed_origins' => ['http://localhost:4200'], 

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['Content-Type', 'Authorization', 'X-Requested-With', 'Origin', 'Accept'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false, 
];
