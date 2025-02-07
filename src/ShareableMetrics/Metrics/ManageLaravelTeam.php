<?php

namespace Spaanproductions\ManageLaravelStats\ShareableMetrics\Metrics;

use Spaanproductions\ManageLaravelStats\ShareableMetrics\Metric;
use Spaanproductions\ManageLaravelStats\ShareableMetrics\CollectableMetric;

class ManageLaravelTeam extends Metric implements CollectableMetric
{
	public function name(): string
	{
		return 'team_id';
	}

	public function value()
	{
		return config('manage-stats.team-id');
	}
}
