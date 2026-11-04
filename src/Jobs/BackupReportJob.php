<?php

namespace Mtvtd\LaravelStats\Jobs;

use Throwable;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Mtvtd\LaravelStats\Backups\BackupInventory;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics\Host;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics\Environment;

/**
 * Sends the backup overview to status.mtvtd.nl after spatie/laravel-backup made (or failed to make) a backup.
 */
class BackupReportJob implements ShouldQueue
{
	use Queueable;
	use Dispatchable;
	use SerializesModels;
	use InteractsWithQueue;

	public $tries = 1;

	/**
	 * @param array{type: string, disk: string|null, name: string|null, error: string|null} $event
	 */
	public function __construct(
		public array $event,
	) {
		//
	}

	public function handle(BackupInventory $inventory): void
	{
		if ( ! config('laravel-stats.backup-reporting-enabled', true)) {
			return;
		}

		if (is_null(config('laravel-stats.token'))) {
			return;
		}

		$payload = [
			'host' => (new Host())->value(),
			'environment' => (new Environment())->value(),
			'backups' => $inventory->destinations(),
			'event' => $this->event,
		];

		try {
			Http::timeout(30)
				->retry(2, 1000)
				->baseUrl(config('laravel-stats.base-url'))
				->withHeaders([
					'Accept' => 'application/json',
				])
				->withToken(config('laravel-stats.token'))
				->post('/api/backups', $payload);
		} catch (Throwable $exception) {
			if (config('laravel-stats.log-exceptions')) {
				if (class_exists('Bugsnag\BugsnagLaravel\Facades\Bugsnag')) {
					\Bugsnag\BugsnagLaravel\Facades\Bugsnag::notifyException($exception);
				} else {
					Log::error($exception->getMessage());
				}
			}
		}
	}

	/** Tags for Laravel Telescope */
	public function tags(): array
	{
		return [
			'job:BackupReportJob',
			'backup:' . ($this->event['type'] ?? 'unknown'),
		];
	}
}
