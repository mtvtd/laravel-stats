<?php

namespace Spaanproductions\ManageLaravelStats\Helpers\Payloads;

class ScheduledTaskFailedPayload extends ScheduledTaskPayload
{
	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			'type' => class_basename($this->event),
			'time' => now()->toIso8601String(),
			'exception' => $this->event->exception->getMessage(),
			'exit_code' => $this->event->task->exitCode,
			'memory' => memory_get_usage(true),
			'task' => (new TaskPayload($this->event->task))->toArray(),
		]);
	}
}
