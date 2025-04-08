<?php

namespace Mtvtd\LaravelStats\ShareableMetrics\Metrics;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Mtvtd\LaravelStats\ShareableMetrics\Metric;
use Mtvtd\LaravelStats\ShareableMetrics\CollectableMetric;

class LaravelAbout extends Metric implements CollectableMetric
{
	public function name(): string
	{
		return 'about';
	}

	public function value()
	{
		if (class_exists('\Illuminate\Foundation\Console\AboutCommand')) {
			$exitcode = Artisan::call('about --json');
			if ($exitcode === Command::SUCCESS) {
				$json = Artisan::output();

				return json_decode($json, true);
			}
		}

		return [];
	}
}
