<?php

namespace Mtvtd\LaravelStats;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Mtvtd\LaravelStats\Commands\LaravelStatsCommand;
use Mtvtd\LaravelStats\Subscribers\ScheduledTaskSubscriber;

class LaravelStatsServiceProvider extends PackageServiceProvider
{
	public function configurePackage(Package $package): void
	{
		/*
		 * This class is a Package Service Provider
		 * More info: https://github.com/spatie/laravel-package-tools
		 */

		$package
			->name('laravel-stats')
			->hasConfigFile('laravel-stats')
			// ->hasViews()
			// ->hasMigration('create_manage-laravel-stats_table')
			->hasCommands([
				LaravelStatsCommand::class,
			]);
	}

	public function bootingPackage(): void
	{
		if ($this->app->runningInConsole()) {
			$this->app->make('events')->subscribe(ScheduledTaskSubscriber::class);
		}
	}
}
