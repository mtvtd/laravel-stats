<?php

namespace Mtvtd\LaravelStats\ShareableMetrics\Metrics;

use Mtvtd\LaravelStats\ShareableMetrics\Metric;
use Mtvtd\LaravelStats\ShareableMetrics\CollectableMetric;

class Url extends Metric implements CollectableMetric
{
	public function name(): string
	{
		return 'url';
	}

	public function value()
	{
		return url('/');
	}
}
