<?php

namespace Spaanproductions\ManageLaravelStats\ShareableMetrics\Metrics;

use Spaanproductions\ManageLaravelStats\ShareableMetrics\Metric;
use Spaanproductions\ManageLaravelStats\ShareableMetrics\CollectableMetric;

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
