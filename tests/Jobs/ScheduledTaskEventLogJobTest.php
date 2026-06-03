<?php

namespace Mtvtd\LaravelStats\Tests\Jobs;

use Illuminate\Support\Facades\Http;
use Mtvtd\LaravelStats\Tests\TestCase;
use Mtvtd\LaravelStats\Jobs\ScheduledTaskEventLogJob;

class ScheduledTaskEventLogJobTest extends TestCase
{
	private function validPayload(): array
	{
		return [
			'type' => 'ScheduledTaskStarting',
			'task' => [
				'command' => 'inspire',
			],
		];
	}

	public function test_does_not_send_http_when_scheduler_logging_disabled()
	{
		Http::fake();
		config()->set('laravel-stats.scheduler-logging-enabled', false);
		config()->set('laravel-stats.token', 'tkn');

		(new ScheduledTaskEventLogJob($this->validPayload()))->handle();

		Http::assertNothingSent();
	}

	public function test_does_not_send_http_when_token_is_null()
	{
		Http::fake();
		config()->set('laravel-stats.scheduler-logging-enabled', true);
		config()->set('laravel-stats.token', null);

		(new ScheduledTaskEventLogJob($this->validPayload()))->handle();

		Http::assertNothingSent();
	}

	public function test_does_not_send_http_when_task_command_is_empty()
	{
		Http::fake();
		config()->set('laravel-stats.scheduler-logging-enabled', true);
		config()->set('laravel-stats.token', 'tkn');

		(new ScheduledTaskEventLogJob([
			'type' => 'ScheduledTaskStarting',
			'task' => ['command' => ''],
		]))->handle();

		Http::assertNothingSent();
	}

	public function test_posts_payload_to_task_event_endpoint_with_bearer_token()
	{
		if ( ! class_exists(\GuzzleHttp\Psr7\Response::class)) {
			$this->markTestSkipped('guzzlehttp/guzzle is required to fake HTTP responses.');
		}

		Http::fake([
			'https://status.test/api/task-event' => Http::response([], 200),
		]);

		config()->set('laravel-stats.scheduler-logging-enabled', true);
		config()->set('laravel-stats.token', 'secret-token');
		config()->set('laravel-stats.base-url', 'https://status.test');

		$payload = $this->validPayload();

		(new ScheduledTaskEventLogJob($payload))->handle();

		Http::assertSent(function ($request) use ($payload) {
			return $request->url() === 'https://status.test/api/task-event'
				&& $request->method() === 'POST'
				&& $request->hasHeader('Authorization', 'Bearer secret-token')
				&& $request->hasHeader('Accept', 'application/json')
				&& $request->data() === $payload;
		});
	}

	public function test_tags_include_job_marker_and_command_name()
	{
		$tags = (new ScheduledTaskEventLogJob($this->validPayload()))->tags();

		$this->assertSame(['job:ScheduledTaskEventLogJob', 'command:inspire'], $tags);
	}

	public function test_tags_fall_back_to_unknown_when_command_is_missing()
	{
		$tags = (new ScheduledTaskEventLogJob([]))->tags();

		$this->assertSame(['job:ScheduledTaskEventLogJob', 'command:unknown'], $tags);
	}
}
