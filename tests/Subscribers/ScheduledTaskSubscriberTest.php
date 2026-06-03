<?php

namespace Mtvtd\LaravelStats\Tests\Subscribers;

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Mtvtd\LaravelStats\Tests\TestCase;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Mtvtd\LaravelStats\Jobs\ScheduledTaskEventLogJob;
use Mtvtd\LaravelStats\Subscribers\ScheduledTaskSubscriber;

class ScheduledTaskSubscriberTest extends TestCase
{
	public function test_subscribe_registers_listeners_for_starting_finished_and_skipped_events()
	{
		foreach ([ScheduledTaskStarting::class, ScheduledTaskFinished::class, ScheduledTaskSkipped::class] as $eventClass) {
			$this->assertTrue(
				Event::hasListeners($eventClass),
				sprintf('Expected a listener for %s.', $eventClass)
			);
		}
	}

	public function test_subscribe_also_registers_failed_listener_when_class_exists()
	{
		if ( ! class_exists(ScheduledTaskFailed::class)) {
			$this->markTestSkipped('ScheduledTaskFailed not available on this Laravel version.');
		}

		$this->assertTrue(Event::hasListeners(ScheduledTaskFailed::class));
	}

	public function test_handle_short_circuits_when_scheduler_logging_disabled()
	{
		config()->set('laravel-stats.scheduler-logging-enabled', false);
		Bus::fake();

		$task = app(Schedule::class)->command('inspire')->daily();
		$event = new ScheduledTaskStarting($task);

		(new ScheduledTaskSubscriber())->handle($event);

		Bus::assertNothingDispatched();
	}

	public function test_handle_dispatches_log_job_with_payload_from_event()
	{
		config()->set('laravel-stats.scheduler-logging-enabled', true);
		Bus::fake();

		$task = app(Schedule::class)->command('inspire')->daily();
		$event = new ScheduledTaskStarting($task);

		(new ScheduledTaskSubscriber())->handle($event);

		Bus::assertDispatched(ScheduledTaskEventLogJob::class, function (ScheduledTaskEventLogJob $job) {
			return is_array($job->payload)
				&& isset($job->payload['type'])
				&& $job->payload['type'] === 'ScheduledTaskStarting'
				&& isset($job->payload['task']['command']);
		});
	}
}
