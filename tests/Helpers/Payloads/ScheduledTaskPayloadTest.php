<?php

namespace Mtvtd\LaravelStats\Tests\Helpers\Payloads;

use Mtvtd\LaravelStats\Tests\TestCase;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Mtvtd\LaravelStats\Helpers\Payloads\ScheduledTaskPayload;
use Mtvtd\LaravelStats\Helpers\Payloads\ScheduledTaskFailedPayload;
use Mtvtd\LaravelStats\Helpers\Payloads\ScheduledTaskSkippedPayload;
use Mtvtd\LaravelStats\Helpers\Payloads\ScheduledTaskFinishedPayload;
use Mtvtd\LaravelStats\Helpers\Payloads\ScheduledTaskStartingPayload;

class ScheduledTaskPayloadTest extends TestCase
{
	public function test_from_event_returns_starting_payload_for_starting_event()
	{
		$task = app(Schedule::class)->command('inspire')->daily();
		$event = new ScheduledTaskStarting($task);

		$this->assertInstanceOf(ScheduledTaskStartingPayload::class, ScheduledTaskPayload::fromEvent($event));
	}

	public function test_from_event_returns_finished_payload_for_finished_event()
	{
		$task = app(Schedule::class)->command('inspire')->daily();
		$event = new ScheduledTaskFinished($task, 1.23);

		$this->assertInstanceOf(ScheduledTaskFinishedPayload::class, ScheduledTaskPayload::fromEvent($event));
	}

	public function test_from_event_returns_skipped_payload_for_skipped_event()
	{
		$task = app(Schedule::class)->command('inspire')->daily();
		$event = new ScheduledTaskSkipped($task);

		$this->assertInstanceOf(ScheduledTaskSkippedPayload::class, ScheduledTaskPayload::fromEvent($event));
	}

	public function test_from_event_returns_failed_payload_for_failed_event_when_class_exists()
	{
		if ( ! class_exists(ScheduledTaskFailed::class)) {
			$this->markTestSkipped('ScheduledTaskFailed not available on this Laravel version.');
		}

		$task = app(Schedule::class)->command('inspire')->daily();
		$event = new ScheduledTaskFailed($task, new \RuntimeException('boom'));

		$this->assertInstanceOf(ScheduledTaskFailedPayload::class, ScheduledTaskPayload::fromEvent($event));
	}

	public function test_from_event_falls_back_to_generic_payload_for_unknown_event()
	{
		$task = app(Schedule::class)->command('inspire')->daily();
		$event = new UnknownScheduledEvent($task);

		$payload = ScheduledTaskPayload::fromEvent($event);

		$this->assertInstanceOf(ScheduledTaskPayload::class, $payload);
		$this->assertNotInstanceOf(ScheduledTaskStartingPayload::class, $payload);
	}

	public function test_base_to_array_contains_expected_keys_and_filters_nulls()
	{
		config()->set('app.url', 'https://example.com');

		$task = app(Schedule::class)->command('inspire')->daily();
		$event = new ScheduledTaskStarting($task);

		$payload = (new ScheduledTaskPayload($event))->toArray();

		foreach (['host', 'environment', 'fingerprint', 'hostname', 'timezone'] as $key) {
			$this->assertArrayHasKey($key, $payload);
			$this->assertNotEmpty($payload[$key]);
		}

		$this->assertSame('example.com', $payload['host']);
	}

	public function test_fingerprint_returns_a_sha1_string()
	{
		$task = app(Schedule::class)->command('inspire')->daily();
		$event = new ScheduledTaskStarting($task);

		$fingerprint = (new ScheduledTaskPayload($event))->fingerprint();

		$this->assertMatchesRegularExpression('/^[a-f0-9]{40}$/', $fingerprint);
	}
}

class UnknownScheduledEvent
{
	public $task;

	public function __construct($task)
	{
		$this->task = $task;
	}
}
