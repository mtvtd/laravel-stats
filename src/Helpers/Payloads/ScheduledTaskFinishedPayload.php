<?php

namespace Mtvtd\LaravelStats\Helpers\Payloads;

class ScheduledTaskFinishedPayload extends ScheduledTaskPayload
{
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'type' => class_basename($this->event),
			'time' => now()->toIso8601String(),
			'runtime' => $this->event->runtime,
			'exit_code' => $this->event->task->exitCode,
			'memory' => memory_get_usage(true),
			'task' => (new TaskPayload($this->event->task))->toArray(),
		]);
	}
}
