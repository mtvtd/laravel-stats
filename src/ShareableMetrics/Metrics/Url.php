<?php

namespace Spaanproductions\ManageLaravelStats\ShareableMetrics\Metrics;

use Spaanproductions\ManageLaravelStats\ShareableMetrics\Metric;
use Spaanproductions\ManageLaravelStats\ShareableMetrics\CollectableMetric;

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
