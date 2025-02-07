<?php

namespace Spaanproductions\ManageLaravelStats\Subscribers;

use Illuminate\Events\Dispatcher;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Spaanproductions\ManageLaravelStats\Jobs\ScheduledTaskEventLogJob;
use Spaanproductions\ManageLaravelStats\Helpers\Payloads\ScheduledTaskPayload;

class ScheduledTaskSubscriber
{
	public function handle($event): void
	{
		$data = ScheduledTaskPayload::fromEvent($event);
		dispatch(new ScheduledTaskEventLogJob($data->toArray()));
	}

	public function subscribe(Dispatcher $events): void
	{
		$events->listen([
			ScheduledTaskStarting::class,
			ScheduledTaskFinished::class,
			ScheduledTaskSkipped::class,
		], static::class . '@handle');

		if (class_exists(ScheduledTaskFailed::class)) {
			$events->listen(ScheduledTaskFailed::class, static::class . '@handle');
		}
	}
}
