<?php

namespace Mtvtd\LaravelStats\ShareableMetrics\Metrics;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Foundation\Console\AboutCommand;
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
		try {
			return $this->getInformation()->toArray();
		} catch (\Throwable $exception) {
			//
		}

		return [];
	}

	public function getInformation(): Collection
	{
		$information = Cache::remember('LSTATS::ABOUT:APPLICATION', now()->addHour(), function () {
			Artisan::call(AboutCommand::class, ['--json' => true]);

			return Artisan::output();
		});

		$information = new Collection(json_decode($information, true, 512, JSON_THROW_ON_ERROR));

		return $this->formatInformation($information);
	}

	public function formatInformation(Collection $information): Collection
	{
		return $information
			->map(function (array $items, string $category) {
				return (new Collection($items))->transform(function (mixed $value, string $key) {
					return match (true) {
						is_bool($value), is_array($value) => $value,
						is_int($value) => number_format($value),
						is_string($value) => blank($value) ? '-' : $value,
						default => (string) $value,
					};
				});
			});
	}
}
