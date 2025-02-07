<?php

namespace Mtvtd\LaravelStats\ShareableMetrics\Metrics;

use Mtvtd\LaravelStats\ShareableMetrics\Metric;
use Mtvtd\LaravelStats\ShareableMetrics\CollectableMetric;

class Name extends Metric implements CollectableMetric
{
	public function name(): string
	{
		return 'name';
	}

	public function value()
	{
		return config('app.name');
	}
}
