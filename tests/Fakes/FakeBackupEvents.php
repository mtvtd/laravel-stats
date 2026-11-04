<?php

namespace Mtvtd\LaravelStats\Tests\Fakes;

use Exception;

/**
 * The shapes of spatie/laravel-backup's BackupWasSuccessful and BackupHasFailed events.
 * v10 has the disk and backup name as strings; v8 and v9 carry a BackupDestination.
 */
class FakeBackupEvents
{
	public static function succeededV10(string $diskName, string $backupName): object
	{
		return (object) ['diskName' => $diskName, 'backupName' => $backupName];
	}

	public static function failedV10(Exception $exception, ?string $diskName = null, ?string $backupName = null): object
	{
		return (object) ['exception' => $exception, 'diskName' => $diskName, 'backupName' => $backupName];
	}

	public static function succeededV9(FakeBackupDestination $destination): object
	{
		return (object) ['backupDestination' => $destination];
	}

	public static function failedV9(Exception $exception, ?FakeBackupDestination $destination = null): object
	{
		return (object) ['exception' => $exception, 'backupDestination' => $destination];
	}
}
