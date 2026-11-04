<?php

/*
 * The backup events as spatie/laravel-backup v10 defines them: disk and backup name as strings.
 */

namespace Spatie\Backup\Events;

use Exception;

class BackupWasSuccessful
{
	public function __construct(public string $diskName, public string $backupName)
	{
	}
}

class BackupHasFailed
{
	public function __construct(public Exception $exception, public ?string $diskName = null, public ?string $backupName = null)
	{
	}
}
