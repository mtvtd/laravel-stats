<?php

namespace Spaanproductions\ManageLaravelStats\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Database\Eloquent\Factories\Factory;
use Spaanproductions\ManageLaravelStats\ManageLaravelStatsServiceProvider;

class TestCase extends Orchestra
{
	public function setUp(): void
	{
		parent::setUp();

		Factory::guessFactoryNamesUsing(
			function (string $modelName) {
				return 'Spaanproductions\\ManageLaravelStats\\Database\\Factories\\' . class_basename($modelName) . 'Factory';
			}
		);
	}

	protected function getPackageProviders($app)
	{
		return [
			ManageLaravelStatsServiceProvider::class,
		];
	}

	public function getEnvironmentSetUp($app)
	{
		config()->set('database.default', 'testing');

		/*
		include_once __DIR__.'/../database/migrations/create_manage-laravel-stats_table.php.stub';
		(new \CreatePackageTable())->up();
		*/
	}
}
