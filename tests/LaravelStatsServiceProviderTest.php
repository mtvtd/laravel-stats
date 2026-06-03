<?php

namespace Mtvtd\LaravelStats\Tests;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Console\Events\ScheduledTaskStarting;

class LaravelStatsServiceProviderTest extends TestCase
{
	public function test_mtvtd_laravel_stats_command_is_registered()
	{
		$this->assertArrayHasKey('mtvtd:laravel-stats', Artisan::all());
	}

	public function test_scheduled_task_subscriber_is_wired_to_the_event_dispatcher()
	{
		$this->assertTrue(Event::hasListeners(ScheduledTaskStarting::class));
	}

	public function test_default_config_values_are_loaded()
	{
		$this->assertSame('https://status.mtvtd.nl', config('laravel-stats.base-url'));
		$this->assertNull(config('laravel-stats.token'));
		$this->assertTrue(config('laravel-stats.scheduler-logging-enabled'));
		$this->assertFalse(config('laravel-stats.log-exceptions'));
	}
}
