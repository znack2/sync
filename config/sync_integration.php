<?php

return [
	'app' => [
		'version' => env('APP_VERSION', 'v1'),
	],
	'connection' => [
        'host' => env('SYNC_ENGINE_ADDR', 'localhost:5555'),
    ]
];
