<?php

namespace Mtvtd\LaravelStats\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Mtvtd\LaravelStats\ShareableMetrics\Metric;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics;

class LaravelStatsCommand extends Command
{
	public $signature = 'mtvtd:laravel-stats {--dry-run}';
	public $description = 'Send all the laravel stats to status.mtvtd.nl.';

	public function handle(): int
	{
		$this->output->title('Uploading all stats to ManageLaravel.');

		$data = collect([
			Metrics\Host::class,
			Metrics\Environment::class,
			Metrics\Name::class,
			Metrics\InstalledVersion::class,
			Metrics\Url::class,
			Metrics\GitInfo::class,
			Metrics\InstalledPackages::class,
			Metrics\PhpVersion::class,
			Metrics\LaravelVersion::class,
			Metrics\ServerInfo::class,
			Metrics\ScheduledTasks::class,
		])->map(function (string $metricClass) {
			return new $metricClass();
		})->map(function (Metric $metric) {
			return $metric->toArray();
		})->mapWithKeys(function ($item) {
			return [
				key($item) => $item[key($item)],
			];
		});

		if ($this->option('dry-run')) {
			dump($data->toArray());

			return self::SUCCESS;
		}

		$response = Http::baseUrl(config('laravel-stats.base-url'))
			->withHeaders([
				'Accept' => 'application/json',
			])
			->withToken(config('laravel-stats.token'))
			->post('/api/stats', $data->toArray());

		if ( ! $response->ok()) {
			$this->error('Something went wrong..');
			$this->error($response->json('message'));

			return self::FAILURE;
		}

		$this->info(sprintf('The current state is "%s", latest Laravel version is %s.', $response->json('status'), $response->json('desired_laravel_version')));

		$this->output->success('All done');

		return self::SUCCESS;
	}
}
