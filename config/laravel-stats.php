<?php

return [
	'base-url' => env('LARAVEL_STATS_BASE_URL', 'https://status.mtvtd.nl'),

    'token' => env('LARAVEL_STATS_TOKEN'),

	'scheduler-logging-enabled' => env('LARAVEL_STATS_SCHEDULER_LOGGING_ENABLED', true),

	'log-exceptions' =>  env('LARAVEL_STATS_LOG_EXCEPTIONS', false),
];
