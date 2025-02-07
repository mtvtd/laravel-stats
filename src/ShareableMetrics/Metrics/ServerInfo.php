<?php

namespace Spaanproductions\ManageLaravelStats\ShareableMetrics\Metrics;

use Spaanproductions\ManageLaravelStats\ShareableMetrics\Metric;
use Spaanproductions\ManageLaravelStats\ShareableMetrics\CollectableMetric;

class ServerInfo extends Metric implements CollectableMetric
{
	public function name(): string
	{
		return 'server-info';
	}

	public function value()
	{
		return [
			'os' => php_uname('s'),
			'hostname' => php_uname('n'),
			'release' => php_uname('r'),
			'version' => php_uname('v'),
			'machine' => php_uname('m'),
		];
	}
}
