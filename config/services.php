<?php

return [

    'glpi' => [
        'url' => env('GLPI_API_URL', 'http://192.168.10.216/api.php/v2'),
        'client_id' => env('GLPI_OAUTH_CLIENT_ID', ''),
        'client_secret' => env('GLPI_OAUTH_CLIENT_SECRET', ''),
        'username' => env('GLPI_OAUTH_USERNAME', ''),
        'password' => env('GLPI_OAUTH_PASSWORD', ''),
        'entity_id' => env('GLPI_ENTITY_ID', null),
        'entity_recursive' => env('GLPI_ENTITY_RECURSIVE', false),
        'category_id' => env('GLPI_CATEGORY_ID', null),
    ],

];