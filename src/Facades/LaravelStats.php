<?php

namespace Mtvtd\LaravelStats\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Mtvtd\LaravelStats\LaravelStats
 */
class LaravelStats extends Facade
{
	protected static function getFacadeAccessor()
	{
		return \Mtvtd\LaravelStats\LaravelStats::class;
	}
}
