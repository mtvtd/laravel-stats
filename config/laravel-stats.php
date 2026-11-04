<?php

return [
	'base-url' => env('LARAVEL_STATS_BASE_URL', 'https://status.mtvtd.nl'),

    'token' => env('LARAVEL_STATS_TOKEN'),

	'scheduler-logging-enabled' => env('LARAVEL_STATS_SCHEDULER_LOGGING_ENABLED', true),

	/*
	 * When the project uses spatie/laravel-backup: send its backups with the stats,
	 * and report every backup run (successful or failed) right away.
	 */
	'backup-reporting-enabled' => env('LARAVEL_STATS_BACKUP_REPORTING_ENABLED', true),

	'backups' => [
		// The newest backups to send per destination.
		'max' => 200,
	],

	'log-exceptions' =>  env('LARAVEL_STATS_LOG_EXCEPTIONS', false),
];
