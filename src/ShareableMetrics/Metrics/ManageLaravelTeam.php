<?php

namespace Mtvtd\LaravelStats\ShareableMetrics\Metrics;

use Mtvtd\LaravelStats\ShareableMetrics\Metric;
use Mtvtd\LaravelStats\ShareableMetrics\CollectableMetric;

class ManageLaravelTeam extends Metric implements CollectableMetric
{
	public function name(): string
	{
		return 'team_id';
	}

	public function value()
	{
		return config('laravel-stats.team-id');
	}
}
