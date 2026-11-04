<?php

namespace Mtvtd\LaravelStats\Tests\Fakes;

use Exception;
use Illuminate\Support\Collection;

/**
 * Stands in for Spatie\Backup\BackupDestination\BackupDestination. Set the disks per test in $disks.
 */
class FakeBackupDestination
{
	/** @var array<string, array{reachable?: bool, error?: string, throws?: bool, backups?: array<int, FakeBackup>}> */
	public static array $disks = [];

	private string $diskName;

	private string $backupName;

	public function __construct(string $diskName, string $backupName)
	{
		$this->diskName = $diskName;
		$this->backupName = $backupName;
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
		return (float) $this->backups()->sum(function (FakeBackup $backup) {
			return $backup->sizeInBytes();
		});
	}
}
