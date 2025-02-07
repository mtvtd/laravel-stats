<?php

namespace Mtvtd\LaravelStats\ShareableMetrics\Metrics;

use Mtvtd\LaravelStats\ShareableMetrics\Metric;
use Mtvtd\LaravelStats\ShareableMetrics\CollectableMetric;

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
