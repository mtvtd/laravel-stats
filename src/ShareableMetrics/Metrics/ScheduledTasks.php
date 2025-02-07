<?php

namespace Spaanproductions\ManageLaravelStats\ShareableMetrics\Metrics;

use Illuminate\Support\Str;
use Illuminate\Console\Application;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Spaanproductions\ManageLaravelStats\ShareableMetrics\Metric;
use Spaanproductions\ManageLaravelStats\ShareableMetrics\CollectableMetric;

class ScheduledTasks extends Metric implements CollectableMetric
{
	public function name(): string
	{
		return 'scheduled-tasks';
	}

	public function value()
	{
		return collect(app(Schedule::class)->events())
			->filter(function (Event $event) {
				return app()->environment($event->environments) || empty($event->environments);
			})
			->reject(function (Event $event) {
				return empty($this->getCommand($event));
			})
			->map(function (Event $event) {
				return [
					'expression' => $event->expression,
					'command' => $this->getCommand($event),
					'description' => $event->description,
					'timezone' => $event->timezone,
					'even_in_maintenance' => $event->evenInMaintenanceMode,
					'without_overlapping' => $event->withoutOverlapping,
					'on_one_server' => $event->onOneServer,
					'run_in_background' => $event->runInBackground,
				];
			})->toArray();
	}

	protected function getCommand(Event $event)
	{
		return collect(explode(' ', $event->command))
			->skipUntil(function ($item) {
				return Str::contains($item, Application::artisanBinary());
			})->skip(1)->implode(' ');
	}
}
