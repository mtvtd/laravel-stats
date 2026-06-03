<?php

namespace Mtvtd\LaravelStats\Tests\Helpers\Payloads;

use Mtvtd\LaravelStats\Tests\TestCase;
use Illuminate\Console\Scheduling\Schedule;
use Mtvtd\LaravelStats\Helpers\Payloads\TaskPayload;

class TaskPayloadTest extends TestCase
{
	public function test_to_array_exposes_full_task_metadata_shape()
	{
		$task = app(Schedule::class)
			->command('inspire')
			->daily()
			->onOneServer()
			->runInBackground()
			->description('Inspire me');

		$payload = (new TaskPayload($task))->toArray();

		$expectedKeys = [
			'timezone', 'type', 'expression', 'command', 'maintenance',
			'without_overlapping', 'on_one_server', 'run_in_background',
			'description', 'mutex', 'filtered', 'extra',
		];

		$this->assertSame($expectedKeys, array_keys($payload));
		// TaskIdentifier returns TYPE_SHELL for artisan commands today
		// because sanitisedCommand strips the 'artisan' token before
		// the contains-check — see TaskIdentifierTest.
		$this->assertSame('shell', $payload['type']);
		$this->assertSame('0 0 * * *', $payload['expression']);
		$this->assertSame('inspire', $payload['command']);
		$this->assertTrue($payload['on_one_server']);
		$this->assertTrue($payload['run_in_background']);
		$this->assertFalse($payload['maintenance']);
		$this->assertFalse($payload['without_overlapping']);
		$this->assertSame('Inspire me', $payload['description']);
		$this->assertStringStartsWith('managelaravel:', $payload['mutex']);
		$this->assertFalse($payload['filtered']);
	}

	public function test_filtered_flag_is_true_when_when_callback_is_attached()
	{
		$task = app(Schedule::class)->command('inspire')->daily()->when(function () {
			return false;
		});

		$payload = (new TaskPayload($task))->toArray();

		$this->assertTrue($payload['filtered']);
	}

	public function test_filtered_flag_is_true_when_skip_callback_is_attached()
	{
		$task = app(Schedule::class)->command('inspire')->daily()->skip(function () {
			return true;
		});

		$payload = (new TaskPayload($task))->toArray();

		$this->assertTrue($payload['filtered']);
	}
}
