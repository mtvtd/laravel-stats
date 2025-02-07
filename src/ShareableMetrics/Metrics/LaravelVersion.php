<?php

namespace Spaanproductions\ManageLaravelStats\ShareableMetrics\Metrics;

use Spaanproductions\ManageLaravelStats\ShareableMetrics\Metric;
use Spaanproductions\ManageLaravelStats\ShareableMetrics\CollectableMetric;

class LaravelVersion extends Metric implements CollectableMetric
{
	public function name(): string
	{
		return 'laravel-version';
	}

	public function value()
	{
		return app()->version();
	}
}
