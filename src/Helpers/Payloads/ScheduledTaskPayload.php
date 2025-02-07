<?php

namespace Spaanproductions\ManageLaravelStats\Helpers\Payloads;

use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Spaanproductions\ManageLaravelStats\Facades\ManageLaravel;

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
			'fingerprint' => $this->fingerprint(),
			'version' => ManageLaravel::version(),
			'hostname' => gethostname(),
			'ip' => request()->getClientIp(),
			'environment' => app()->environment(),
			'timezone' => (string)now()->timezone,
		]);
	}

	public function fingerprint(): string
	{
		return sha1(vsprintf('%s.%s.%s.%s.%s', [
			config('manage-stats.team-id'),
			ManageLaravel::fingerprintTask($this->event->task),
			getmypid(),
			spl_object_id($this->event->task),
			spl_object_hash($this->event->task),
		]));
	}

	public static function fromEvent($event)
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
