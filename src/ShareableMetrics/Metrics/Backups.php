<?php

namespace Mtvtd\LaravelStats\ShareableMetrics\Metrics;

use Mtvtd\LaravelStats\Backups\BackupInventory;
use Mtvtd\LaravelStats\ShareableMetrics\Metric;
use Mtvtd\LaravelStats\ShareableMetrics\CollectableMetric;

/**
 * The spatie/laravel-backup destinations and their backups, or null when that package is not used.
 */
class Backups extends Metric implements CollectableMetric
{
	public function name(): string
	{
		return 'backups';
	}

	public function value()
	{
		if ( ! config('laravel-stats.backup-reporting-enabled', true)) {
			return null;
		}

		$inventory = app(BackupInventory::class);

		return $inventory->available() ? $inventory->destinations() : null;
	}
}
