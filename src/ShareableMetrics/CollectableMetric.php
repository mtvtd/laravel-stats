<?php

namespace Spaanproductions\ManageLaravelStats\ShareableMetrics;

interface CollectableMetric
{
	public function name(): string;

	public function value();
}
