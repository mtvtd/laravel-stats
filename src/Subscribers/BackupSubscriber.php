<?php

namespace Mtvtd\LaravelStats\Subscribers;

use Illuminate\Events\Dispatcher;
use Mtvtd\LaravelStats\Jobs\BackupReportJob;

/**
 * Reports every spatie/laravel-backup run to status.mtvtd.nl. Listens only when that package is installed.
 */
class BackupSubscriber
{
	public const SUCCEEDED = 'Spatie\Backup\Events\BackupWasSuccessful';

	public const FAILED = 'Spatie\Backup\Events\BackupHasFailed';

	public function handle($event): void
	{
		if ( ! config('laravel-stats.backup-reporting-enabled', true)) {
			return;
		}

		[$disk, $name] = $this->destination($event);

		// Only BackupHasFailed carries an exception, in every laravel-backup version.
		$failed = isset($event->exception);

		dispatch(new BackupReportJob([
			'type' => $failed ? 'failed' : 'succeeded',
			'disk' => $disk,
			'name' => $name,
			'error' => $failed ? $event->exception->getMessage() : null,
		]));
	}

	/**
	 * The disk and backup name of the event: laravel-backup v10 has them as properties,
	 * v8 and v9 carry a BackupDestination (which can be null on a failure).
	 *
	 * @return array{0: string|null, 1: string|null}
	 */
	public function destination($event): array
	{
		if (property_exists($event, 'diskName')) {
			return [$event->diskName ?? null, $event->backupName ?? null];
		}

		$destination = $event->backupDestination ?? null;

		if ($destination === null) {
			return [null, null];
		}

		return [$destination->diskName(), $destination->backupName()];
	}

	public function subscribe(Dispatcher $events): void
	{
		foreach ([self::SUCCEEDED, self::FAILED] as $event) {
			if (class_exists($event)) {
				$events->listen($event, static::class . '@handle');
			}
		}
	}
}
