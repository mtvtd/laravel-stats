<?php

namespace Mtvtd\LaravelStats\ShareableMetrics;

abstract class Metric implements CollectableMetric
{
	public function toArray()
	{
		return [
			$this->name() => $this->value(),
		];
	}
}
