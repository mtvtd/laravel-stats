<?php

namespace Mtvtd\LaravelStats\ShareableMetrics;

interface CollectableMetric
{
	public function name(): string;

	public function value();
}
