<?php

namespace Mtvtd\LaravelStats\Backups;

use Throwable;

/**
 * Lists the backups of spatie/laravel-backup (v8, v9 and v10) per destination disk.
 * Returns nothing when the package is not installed.
 */
class BackupInventory
{
	public const DESTINATION_CLASS = 'Spatie\Backup\BackupDestination\BackupDestination';

	/** @var string The BackupDestination class; the tests pass a fake one. */
	protected $destinationClass;

	public function __construct(?string $destinationClass = null)
	{
		$this->destinationClass = $destinationClass ?? self::DESTINATION_CLASS;
	}

	public function available(): bool
	{
		return class_exists($this->destinationClass) && config('backup.backup.name') !== null;
	}

	/**
	 * @return array<int, array{disk: string, name: string, reachable: bool, used_storage: float|null, error: string|null, backups: array<int, array{path: string, date: string, size: float|null}>}>
	 */
	public function destinations(): array
	{
		if ( ! $this->available()) {
			return [];
		}

		$name = (string) config('backup.backup.name');
		$destinationClass = $this->destinationClass;

		return collect(config('backup.backup.destination.disks', []))
			->map(function ($disk) use ($name, $destinationClass) {
				$disk = (string) $disk;

				try {
					$destination = $destinationClass::create($disk, $name);

					if ( ! $destination->isReachable()) {
						$error = $destination->connectionError();

						return $this->destination($disk, $name, false, null, $error ? $error->getMessage() : 'De backup-disk is niet bereikbaar.', []);
					}

					$backups = collect($destination->backups())
						->take((int) config('laravel-stats.backups.max', 200))
						->map(function ($backup) {
							return [
								'path' => $backup->path(),
								'date' => $backup->date()->toIso8601String(),
								'size' => $this->size($backup),
							];
						})
						->values()
						->all();

					return $this->destination($disk, $name, true, $this->usedStorage($destination), null, $backups);
				} catch (Throwable $exception) {
					return $this->destination($disk, $name, false, null, $exception->getMessage(), []);
				}
			})
			->values()
			->all();
	}

	private function destination(string $disk, string $name, bool $reachable, ?float $usedStorage, ?string $error, array $backups): array
	{
		return [
			'disk' => $disk,
			'name' => $name,
			'reachable' => $reachable,
			'used_storage' => $usedStorage,
			'error' => $error,
			'backups' => $backups,
		];
	}

	private function size($backup): ?float
	{
		try {
			// sizeInBytes() since laravel-backup v7, size() before.
			if (method_exists($backup, 'sizeInBytes')) {
				return (float) $backup->sizeInBytes();
			}

			if (method_exists($backup, 'size')) {
				return (float) $backup->size();
			}
		} catch (Throwable $exception) {
			//
		}

		return null;
	}

	private function usedStorage($destination): ?float
	{
		try {
			return method_exists($destination, 'usedStorage') ? (float) $destination->usedStorage() : null;
		} catch (Throwable $exception) {
			return null;
		}
	}
}
