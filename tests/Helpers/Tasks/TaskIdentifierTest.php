<?php

namespace Mtvtd\LaravelStats\Tests\Helpers\Tasks;

use Mtvtd\LaravelStats\Tests\TestCase;
use Illuminate\Console\Scheduling\Schedule;
use Mtvtd\LaravelStats\Helpers\Tasks\TaskIdentifier;

class TaskIdentifierTest extends TestCase
{
	/**
	 * Artisan-scheduled events are classified as TYPE_SHELL today because
	 * sanitisedCommand() strips the 'artisan' substring before the
	 * `Str::contains(..., 'artisan')` check runs — so TYPE_COMMAND is
	 * never reached via the public scheduler API. This test pins that
	 * current behaviour; if TaskIdentifier is fixed, update this test.
	 *
	 */
	public function test_regular_artisan_event_is_currently_identified_as_shell()
	{
		$event = app(Schedule::class)->command('inspire')->daily();

		$this->assertSame(TaskIdentifier::TYPE_SHELL, (new TaskIdentifier())($event));
	}

	public function test_shell_event_is_identified_as_shell()
	{
		$event = app(Schedule::class)->exec('ls -la')->daily();

		$this->assertSame(TaskIdentifier::TYPE_SHELL, (new TaskIdentifier())($event));
	}

	public function test_callback_event_with_class_name_description_is_identified_as_job()
	{
		$event = app(Schedule::class)
			->call(function () {
				// noop
			})
			->daily()
			->description(SampleJob::class);

		$this->assertSame(TaskIdentifier::TYPE_JOB, (new TaskIdentifier())($event));
	}

	public function test_callback_event_with_default_summary_is_identified_as_closure()
	{
		$event = app(Schedule::class)->call(function () {
			// noop
		})->daily();

		$this->assertSame(TaskIdentifier::TYPE_CLOSURE, (new TaskIdentifier())($event));
	}
}

class SampleJob
{
	public function handle(): void
	{
		// noop
	}
}
