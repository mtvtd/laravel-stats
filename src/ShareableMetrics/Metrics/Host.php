<?php

namespace Mtvtd\LaravelStats\ShareableMetrics\Metrics;

use Mtvtd\LaravelStats\ShareableMetrics\Metric;
use Mtvtd\LaravelStats\ShareableMetrics\CollectableMetric;

class Host extends Metric implements CollectableMetric
{
	public function name(): string
	{
		return 'host';
	}

	public function value()
	{
		return parse_url(config('app.url'), PHP_URL_HOST);
	}
}
