<?php

namespace Mtvtd\LaravelStats\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\Client\HttpClientException;

class ScheduledTaskEventLogJob implements ShouldQueue
{
	use Queueable;
	use Dispatchable;
	use SerializesModels;
	use InteractsWithQueue;

	public $tries = 1;

	public function __construct(
		public array $payload,
	) {
		//
	}

	public function handle(): void
	{
		if ( ! config('laravel-stats.scheduler-logging-enabled')) {
			return;
		}

		if (is_null(config('laravel-stats.token'))) {
			return;
		}

		if (empty(data_get($this->payload, 'task.command'))) {
			return;
		}

		try {
			Http::timeout(5)
				->retry(2, 10)
				->baseUrl(config('laravel-stats.base-url'))
				->withHeaders([
					'Accept' => 'application/json',
				])
				->withToken(config('laravel-stats.token'))
				->post('/api/task-event', $this->payload);
		} catch (HttpClientException $exception) {
			// Do nothing.
		}
	}
}
