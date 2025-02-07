<?php

namespace Mtvtd\LaravelStats;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Mtvtd\LaravelStats\LaravelStats
 */
class LaravelStatsFacade extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'manage-laravel-stats';
	}
}
