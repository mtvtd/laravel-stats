<?php

namespace Spaanproductions\ManageLaravelStats;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Spaanproductions\ManageLaravelStats\ManageLaravelStats
 */
class ManageLaravelStatsFacade extends Facade
{
	protected static function getFacadeAccessor()
	{
		return 'manage-laravel-stats';
	}
}
