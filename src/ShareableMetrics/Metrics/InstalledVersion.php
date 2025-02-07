<?php

namespace Mtvtd\LaravelStats\ShareableMetrics\Metrics;

use Composer\InstalledVersions;
use Mtvtd\LaravelStats\ShareableMetrics\Metric;
use Mtvtd\LaravelStats\ShareableMetrics\CollectableMetric;

class InstalledVersion extends Metric implements CollectableMetric
{
	public function name(): string
	{
		return 'version';
	}

	public function value()
	{
		return InstalledVersions::getVersion('Mtvtd/manage-laravel-stats');
	}
}
