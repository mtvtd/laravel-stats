<?php

namespace Spaanproductions\ManageLaravelStats\ShareableMetrics\Metrics;

use Composer\InstalledVersions;
use Spaanproductions\ManageLaravelStats\ShareableMetrics\Metric;
use Spaanproductions\ManageLaravelStats\ShareableMetrics\CollectableMetric;

class InstalledVersion extends Metric implements CollectableMetric
{
	public function name(): string
	{
		return 'version';
	}

	public function value()
	{
		return InstalledVersions::getVersion('spaanproductions/manage-laravel-stats');
	}
}
