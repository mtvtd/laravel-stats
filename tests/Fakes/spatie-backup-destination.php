<?php

/*
 * Minimal stand-ins for spatie/laravel-backup's BackupDestination and Backup, so the backup reporting
 * can be tested without installing that package. Load only in tests that run in a separate process.
 */

namespace Spatie\Backup\BackupDestination;

use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class Backup
{
	public function __construct(private string $path, private Carbon $date, private float $size)
	{
	}

	public function path(): string
	{
		return $this->path;
	}

	public function date(): Carbon
	{
		return $this->date;
	}

	public function sizeInBytes(): float
	{
		return $this->size;
	}
}

class BackupDestination
{
	/** @var array<string, array{reachable: bool, error?: string, backups?: array<int, Backup>}> */
	public static array $disks = [];

	public function __construct(private string $diskName, private string $backupName)
	{
	}

	public static function create(string $diskName, string $backupName): self
	{
		if ((static::$disks[$diskName]['throws'] ?? false) === true) {
			throw new Exception('Disk [' . $diskName . '] does not have a configured driver.');
		}

		return new self($diskName, $backupName);
	}

	public function diskName(): string
	{
		return $this->diskName;
	}

	public function backupName(): string
	{
		return $this->backupName;
	}

	public function isReachable(): bool
	{
		return static::$disks[$this->diskName]['reachable'] ?? true;
	}

	public function connectionError(): ?Exception
	{
		$error = static::$disks[$this->diskName]['error'] ?? null;

		return $error ? new Exception($error) : null;
	}

	public function backups(): Collection
	{
		return new Collection(static::$disks[$this->diskName]['backups'] ?? []);
	}

	public function usedStorage(): float
	{
		return (float) $this->backups()->sum(function (Backup $backup) {
			return $backup->sizeInBytes();
		});
	}
}
