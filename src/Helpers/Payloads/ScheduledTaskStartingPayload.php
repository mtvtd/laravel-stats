<?php

namespace Mtvtd\LaravelStats\Helpers\Payloads;

class ScheduledTaskStartingPayload extends ScheduledTaskPayload
{
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'type' => class_basename($this->event),
			'time' => now()->toIso8601String(),
			'expires' => now()->addMinutes($this->event->task->expiresAt)->toIso8601String(),
			'memory' => memory_get_usage(true),
			'task' => (new TaskPayload($this->event->task))->toArray(),
		]);
	}
}
