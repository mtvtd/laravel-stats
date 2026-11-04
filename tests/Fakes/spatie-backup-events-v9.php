<?php

/*
 * The backup events as spatie/laravel-backup v8 and v9 define them: they carry a BackupDestination.
 */

namespace Spatie\Backup\Events;

use Exception;
use Spatie\Backup\BackupDestination\BackupDestination;

class BackupWasSuccessful
{
	public function __construct(public BackupDestination $backupDestination)
	{
	}
}

class BackupHasFailed
{
	public function __construct(public Exception $exception, public ?BackupDestination $backupDestination = null)
	{
	}
}
