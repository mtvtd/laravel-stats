<?php

namespace Spaanproductions\ManageLaravelStats\Helpers\Payloads;

class ScheduledTaskSkippedPayload extends ScheduledTaskPayload
{
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'type' => class_basename($this->event),
			'time' => now()->toIso8601String(),
			'task' => (new TaskPayload($this->event->task))->toArray(),
		]);
	}
}
