<?php

namespace Spaanproductions\ManageLaravelStats;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spaanproductions\ManageLaravelStats\Commands\ManageLaravelStatsCommand;
use Spaanproductions\ManageLaravelStats\Subscribers\ScheduledTaskSubscriber;

class ManageLaravelStatsServiceProvider extends PackageServiceProvider
{
	public function configurePackage(Package $package): void
	{
		/*
		 * This class is a Package Service Provider
		 * More info: https://github.com/spatie/laravel-package-tools
		 */

		$package
			->name('manage-laravel-stats')
			->hasConfigFile('manage-stats')
			// ->hasViews()
			// ->hasMigration('create_manage-laravel-stats_table')
			->hasCommands([
				ManageLaravelStatsCommand::class,
			]);
	}

	public function bootingPackage(): void
	{
		if ($this->app->runningInConsole()) {
			$this->app->make('events')->subscribe(ScheduledTaskSubscriber::class);
		}
	}
}
