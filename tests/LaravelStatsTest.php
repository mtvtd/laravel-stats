<?php

namespace Mtvtd\LaravelStats\Tests;

use Composer\InstalledVersions;
use Mtvtd\LaravelStats\LaravelStats;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Console\Scheduling\CallbackEvent;

class LaravelStatsTest extends TestCase
{
	public function test_version_returns_the_installed_composer_version()
	{
		$expected = InstalledVersions::getVersion('mtvtd/laravel-stats');

		$this->assertSame($expected, (new LaravelStats())->version());
	}

	public function test_sanitised_command_strips_quotes_php_binary_and_artisan_token()
	{
		$stats = new LaravelStats();

		$command = sprintf("'%s' 'artisan' inspire", PHP_BINARY);

		$this->assertSame('inspire', $stats->sanitisedCommand($command));
		$this->assertSame('', $stats->sanitisedCommand(null));
		$this->assertSame('', $stats->sanitisedCommand(''));
		$this->assertSame('schedule:run', $stats->sanitisedCommand('"artisan" schedule:run'));
	}

	public function test_fingerprint_task_returns_stable_managelaravel_prefixed_sha1()
	{
		$event = app(Schedule::class)->command('inspire')->daily();

		$first = (new LaravelStats())->fingerprintTask($event);
		$second = (new LaravelStats())->fingerprintTask($event);

		$this->assertSame($first, $second);
		$this->assertStringStartsWith('managelaravel:', $first);
		$this->assertSame(40, strlen(substr($first, strlen('managelaravel:'))));
	}

	public function test_fingerprint_callback_event_includes_string_command_in_hash()
	{
		$schedule = app(Schedule::class);
		$eventA = $schedule->call('Foo@bar')->everyMinute();
		$eventB = $schedule->call('Foo@baz')->everyMinute();

		$stats = new LaravelStats();

		$this->assertNotSame(
			$stats->fingerprintTask($eventA),
			$stats->fingerprintTask($eventB)
		);
	}

	public function test_fingerprint_callback_event_sets_extra_with_file_and_line_for_closures()
	{
		$event = app(Schedule::class)->call(function () {
			return 'noop';
		})->everyMinute();

		$fingerprint = (new LaravelStats())->fingerprintTask($event);

		$this->assertStringStartsWith('managelaravel:', $fingerprint);
		$this->assertIsArray((array) $event->extra);
		$this->assertArrayHasKey('file', (array) $event->extra);
		$this->assertArrayHasKey('line', (array) $event->extra);
		$this->assertMatchesRegularExpression('/^\d+ to \d+$/', $event->extra['line']);
	}

	public function test_fingerprint_callback_event_uses_class_name_for_invokable_object()
	{
		$schedule = app(Schedule::class);
		$invokable = new InvokableCallback();
		$event = $schedule->call($invokable)->everyMinute();

		$fingerprint = (new LaravelStats())->fingerprintTask($event);

		$expected = sprintf(
			'managelaravel:%s',
			sha1(str_replace('..', '.', $event->expression . '.' . get_class($invokable) . '.' . $event->description))
		);

		$this->assertSame($expected, $fingerprint);
	}

	public function test_fingerprint_task_for_non_callback_event_uses_expression_command_and_description()
	{
		$event = app(Schedule::class)->command('inspire')->daily()->description('Inspire me');

		$expected = sprintf(
			'managelaravel:%s',
			sha1(trim($event->expression . '.' . $event->command . '.' . $event->description, '.'))
		);

		$this->assertSame($expected, (new LaravelStats())->fingerprintTask($event));
	}
}

class InvokableCallback
{
	public function __invoke()
	{
		return 'invoked';
	}
}
