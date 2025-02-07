<?php

namespace Spaanproductions\ManageLaravelStats\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \Spaanproductions\ManageLaravelStats\ManageLaravelStats
 */
class ManageLaravel extends Facade
{
	protected static function getFacadeAccessor()
	{
		return \Spaanproductions\ManageLaravelStats\ManageLaravelStats::class;
	}
}
