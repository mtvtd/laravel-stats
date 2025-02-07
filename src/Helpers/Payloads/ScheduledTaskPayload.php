<?php

namespace Mtvtd\LaravelStats\Helpers\Payloads;

use Mtvtd\LaravelStats\Facades\LaravelStats;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;

class ScheduledTaskPayload
{
	public $event;

	public function __construct($event)
	{
		$this->event = $event;
	}

	public function toArray(): array
	{
		return array_filter([
			'host' => parse_url(config('app.url'), PHP_URL_HOST),
			'environment' => app()->environment(),
			'fingerprint' => $this->fingerprint(),
			'version' => LaravelStats::version(),
			'hostname' => gethostname(),
			'ip' => request()->getClientIp(),
			'timezone' => (string)now()->timezone,
		]);
	}

	public function fingerprint(): string
	{
		return sha1(vsprintf('%s.%s.%s.%s.%s.%s', [
			parse_url(config('app.url'), PHP_URL_HOST),
			app()->environment(),
			LaravelStats::fingerprintTask($this->event->task),
			getmypid(),
			spl_object_id($this->event->task),
			spl_object_hash($this->event->task),
		]));
	}

	public static function fromEvent($event): ScheduledTaskPayload
	{
		if ($event instanceof ScheduledTaskStarting) {
			return new ScheduledTaskStartingPayload($event);
		}

		if ($event instanceof ScheduledTaskFinished) {
			return new ScheduledTaskFinishedPayload($event);
		}

		if ($event instanceof ScheduledTaskSkipped) {
			return new ScheduledTaskSkippedPayload($event);
		}

		if ($event instanceof ScheduledTaskFailed) {
			return new ScheduledTaskFailedPayload($event);
		}

		return new self($event);
	}
}
