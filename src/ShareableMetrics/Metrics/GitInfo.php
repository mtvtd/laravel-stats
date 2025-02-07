<?php

namespace Mtvtd\LaravelStats\ShareableMetrics\Metrics;

use Mtvtd\LaravelStats\ShareableMetrics\Metric;
use Mtvtd\LaravelStats\ShareableMetrics\CollectableMetric;
use Mtvtd\LaravelStats\Helpers\SourceControl\GitInfoCollector;

class GitInfo extends Metric implements CollectableMetric
{
	public function name(): string
	{
		return 'git-info';
	}

	public function value()
	{
		return resolve(GitInfoCollector::class)->collect()->toArray();
	}
}
