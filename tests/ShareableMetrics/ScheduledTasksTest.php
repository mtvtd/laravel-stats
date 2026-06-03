<?php

namespace Mtvtd\LaravelStats\Tests\ShareableMetrics;

use Mtvtd\LaravelStats\Tests\TestCase;
use Illuminate\Console\Scheduling\Schedule;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics\ScheduledTasks;

class ScheduledTasksTest extends TestCase
{
	public function test_it_returns_a_payload_per_artisan_event_with_command_stripped_of_binary()
	{
		$schedule = app(Schedule::class);
		$schedule->command('inspire')->daily()->description('Inspire me');

		$tasks = (new ScheduledTasks())->value();

		$this->assertCount(1, $tasks);

		$task = $tasks[0];

		$this->assertSame('0 0 * * *', $task['expression']);
		$this->assertSame('inspire', $task['command']);
		$this->assertSame('Inspire me', $task['description']);
		$this->assertSame(
			['expression', 'command', 'description', 'timezone', 'even_in_maintenance', 'without_overlapping', 'on_one_server', 'run_in_background'],
			array_keys($task)
		);
	}

	public function test_metric_name_is_scheduled_tasks()
	{
		$this->assertSame('scheduled-tasks', (new ScheduledTasks())->name());
	}

	public function test_events_without_an_artisan_binary_token_are_filtered_out()
	{
		$schedule = app(Schedule::class);
		$schedule->exec('ls -la')->daily();

		$this->assertSame([], (new ScheduledTasks())->value());
	}

	public function test_events_scoped_to_a_different_environment_are_excluded()
	{
		$schedule = app(Schedule::class);
		$schedule->command('inspire')->daily()->environments('production');

		$this->assertSame([], (new ScheduledTasks())->value());
	}

	public function test_events_with_matching_environment_are_included()
	{
		config()->set('app.env', 'testing');
		$schedule = app(Schedule::class);
		$schedule->command('inspire')->daily()->environments('testing');

		$this->assertCount(1, (new ScheduledTasks())->value());
	}
}
