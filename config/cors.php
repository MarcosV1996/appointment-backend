<?php

return [
'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'logout', 'storage/*', 'register','forgot-password','reset-password','reports*', 'users*',],
'allowed_methods' => ['*'],
'allowed_origins' => ['http://localhost:8080'],
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => true,
];
