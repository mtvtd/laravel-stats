<?php

namespace Mtvtd\LaravelStats\ShareableMetrics\Metrics;

use Mtvtd\LaravelStats\ShareableMetrics\Metric;
use Mtvtd\LaravelStats\ShareableMetrics\CollectableMetric;

class PhpVersion extends Metric implements CollectableMetric
{
	public function name(): string
	{
		return 'php-version';
	}

	public function value()
	{
		return phpversion();
	}
}
