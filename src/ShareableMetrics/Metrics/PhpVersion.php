<?php

namespace Spaanproductions\ManageLaravelStats\ShareableMetrics\Metrics;

use Spaanproductions\ManageLaravelStats\ShareableMetrics\Metric;
use Spaanproductions\ManageLaravelStats\ShareableMetrics\CollectableMetric;

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
