<?php

namespace Mtvtd\LaravelStats\Tests\Helpers\Payloads;

use Mtvtd\LaravelStats\Tests\TestCase;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Mtvtd\LaravelStats\Helpers\Payloads\ScheduledTaskFailedPayload;
use Mtvtd\LaravelStats\Helpers\Payloads\ScheduledTaskSkippedPayload;
use Mtvtd\LaravelStats\Helpers\Payloads\ScheduledTaskStartingPayload;
use Mtvtd\LaravelStats\Helpers\Payloads\ScheduledTaskFinishedPayload;

class EventPayloadsTest extends TestCase
{
	public function test_starting_payload_includes_type_time_expires_memory_and_task()
	{
		$task = app(Schedule::class)->command('inspire')->daily();
		$event = new ScheduledTaskStarting($task);

		$payload = (new ScheduledTaskStartingPayload($event))->toArray();

		$this->assertSame('ScheduledTaskStarting', $payload['type']);
		$this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T/', $payload['time']);
		$this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T/', $payload['expires']);
		$this->assertIsInt($payload['memory']);
		$this->assertIsArray($payload['task']);
		$this->assertArrayHasKey('command', $payload['task']);
	}

	public function test_finished_payload_includes_runtime_exit_code_and_task()
	{
		$task = app(Schedule::class)->command('inspire')->daily();
		$task->exitCode = 0;
		$event = new ScheduledTaskFinished($task, 1.23);

		$payload = (new ScheduledTaskFinishedPayload($event))->toArray();

		$this->assertSame('ScheduledTaskFinished', $payload['type']);
		$this->assertSame(1.23, $payload['runtime']);
		$this->assertSame(0, $payload['exit_code']);
		$this->assertIsInt($payload['memory']);
		$this->assertIsArray($payload['task']);
	}

	public function test_skipped_payload_includes_type_time_and_task_but_no_runtime()
	{
		$task = app(Schedule::class)->command('inspire')->daily();
		$event = new ScheduledTaskSkipped($task);

		$payload = (new ScheduledTaskSkippedPayload($event))->toArray();

		$this->assertSame('ScheduledTaskSkipped', $payload['type']);
		$this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T/', $payload['time']);
		$this->assertIsArray($payload['task']);
		$this->assertArrayNotHasKey('runtime', $payload);
	}

	public function test_failed_payload_includes_exception_message_and_exit_code()
	{
		if ( ! class_exists(ScheduledTaskFailed::class)) {
			$this->markTestSkipped('ScheduledTaskFailed not available on this Laravel version.');
		}

		$task = app(Schedule::class)->command('inspire')->daily();
		$task->exitCode = 137;
		$event = new ScheduledTaskFailed($task, new \RuntimeException('boom'));

		$payload = (new ScheduledTaskFailedPayload($event))->toArray();

		$this->assertSame('ScheduledTaskFailed', $payload['type']);
		$this->assertSame('boom', $payload['exception']);
		$this->assertSame(137, $payload['exit_code']);
		$this->assertIsInt($payload['memory']);
		$this->assertIsArray($payload['task']);
	}
}
