<?php

namespace Mtvtd\LaravelStats\Tests\Commands;

use Illuminate\Support\Facades\Http;
use Mtvtd\LaravelStats\Tests\TestCase;
use Illuminate\Http\Client\ConnectionException;
use Symfony\Component\VarDumper\VarDumper;
use Mtvtd\LaravelStats\Commands\LaravelStatsCommand;

class LaravelStatsCommandTest extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		config()->set('laravel-stats.base-url', 'https://status.test');
		config()->set('laravel-stats.token', 'secret-token');
	}

	public function test_it_posts_the_full_metric_payload_to_the_stats_endpoint()
	{
		if ( ! class_exists(\GuzzleHttp\Psr7\Response::class)) {
			$this->markTestSkipped('guzzlehttp/guzzle is required to fake HTTP responses.');
		}

		Http::fake([
			'https://status.test/api/stats' => Http::response([
				'status' => 'ok',
				'desired_laravel_version' => '11.0.0',
			], 200),
		]);

		$this->artisan('mtvtd:laravel-stats')->assertExitCode(0);

		Http::assertSent(function ($request) {
			if ($request->url() !== 'https://status.test/api/stats') {
				return false;
			}

			if ( ! $request->hasHeader('Authorization', 'Bearer secret-token')) {
				return false;
			}

			$body = $request->data();

			$expectedKeys = [
				'host', 'environment', 'name', 'version', 'url', 'packages',
				'php-version', 'laravel-version', 'server-info',
				'scheduled-tasks', 'about',
			];

			foreach ($expectedKeys as $key) {
				if ( ! array_key_exists($key, $body)) {
					return false;
				}
			}

			return true;
		});
	}

	public function test_dry_run_returns_success_without_issuing_http()
	{
		Http::fake();

		$originalHandler = VarDumper::setHandler(function () {
			// suppress dump() output for this test only
		});

		try {
			$this->artisan('mtvtd:laravel-stats', ['--dry-run' => true])->assertExitCode(0);
		} finally {
			VarDumper::setHandler($originalHandler);
		}

		Http::assertNothingSent();
	}

	public function test_it_fails_on_non_2xx_response()
	{
		if ( ! class_exists(\GuzzleHttp\Psr7\Response::class)) {
			$this->markTestSkipped('guzzlehttp/guzzle is required to fake HTTP responses.');
		}

		Http::fake([
			'https://status.test/api/stats' => Http::response(['message' => 'nope'], 500),
		]);

		$this->artisan('mtvtd:laravel-stats')->assertExitCode(1);
	}

	public function test_it_fails_on_http_exception()
	{
		Http::fake(function () {
			throw new ConnectionException('boom');
		});

		$this->artisan('mtvtd:laravel-stats')->assertExitCode(1);
	}

	public function test_command_collects_the_documented_metric_list()
	{
		/* Regression guard for the hand-maintained list in handle() — if a
		 * metric is added or removed there, update this list too. */
		$path = (new \ReflectionClass(LaravelStatsCommand::class))->getFileName();
		$source = file_get_contents($path);

		$expected = [
			'Metrics\\Host::class',
			'Metrics\\Environment::class',
			'Metrics\\Name::class',
			'Metrics\\InstalledVersion::class',
			'Metrics\\Url::class',
			'Metrics\\InstalledPackages::class',
			'Metrics\\PhpVersion::class',
			'Metrics\\LaravelVersion::class',
			'Metrics\\ServerInfo::class',
			'Metrics\\ScheduledTasks::class',
			'Metrics\\LaravelAbout::class',
		];

		foreach ($expected as $needle) {
			$this->assertStringContainsString($needle, $source, "Expected metric reference $needle in command source.");
		}
	}
}
