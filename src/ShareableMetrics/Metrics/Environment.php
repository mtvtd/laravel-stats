<?php

namespace Mtvtd\LaravelStats\ShareableMetrics\Metrics;

use Mtvtd\LaravelStats\ShareableMetrics\Metric;
use Mtvtd\LaravelStats\ShareableMetrics\CollectableMetric;

class Environment extends Metric implements CollectableMetric
{
	public function name(): string
	{
		return 'environment';
	}

	public function value()
	{
		return config('app.env');
	}
}
