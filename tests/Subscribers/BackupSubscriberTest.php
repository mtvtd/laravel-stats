<?php

namespace Mtvtd\LaravelStats\Tests\Subscribers;

use Exception;
use Illuminate\Support\Facades\Bus;
use Mtvtd\LaravelStats\Tests\TestCase;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Mtvtd\LaravelStats\Jobs\BackupReportJob;
use Mtvtd\LaravelStats\Subscribers\BackupSubscriber;

class BackupSubscriberTest extends TestCase
{
	private function listen(string $fakes): void
	{
		require_once __DIR__ . '/../Fakes/spatie-backup-destination.php';
		require_once __DIR__ . '/../Fakes/' . $fakes;

		// The service provider booted before the fake event classes existed.
		$this->app->make('events')->subscribe(BackupSubscriber::class);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	#[RunInSeparateProcess]
	#[PreserveGlobalState(false)]
	public function test_a_successful_backup_is_reported_with_laravel_backup_v10()
	{
		$this->listen('spatie-backup-events-v10.php');
		Bus::fake();

		event(new \Spatie\Backup\Events\BackupWasSuccessful('s3', 'Clothing4U'));

		Bus::assertDispatched(BackupReportJob::class, function (BackupReportJob $job) {
			return $job->event === ['type' => 'succeeded', 'disk' => 's3', 'name' => 'Clothing4U', 'error' => null];
		});
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	#[RunInSeparateProcess]
	#[PreserveGlobalState(false)]
	public function test_a_failed_backup_is_reported_with_its_error_with_laravel_backup_v10()
	{
		$this->listen('spatie-backup-events-v10.php');
		Bus::fake();

		event(new \Spatie\Backup\Events\BackupHasFailed(new Exception('mysqldump: Access denied')));

		Bus::assertDispatched(BackupReportJob::class, function (BackupReportJob $job) {
			return $job->event === ['type' => 'failed', 'disk' => null, 'name' => null, 'error' => 'mysqldump: Access denied'];
		});
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	#[RunInSeparateProcess]
	#[PreserveGlobalState(false)]
	public function test_backups_are_reported_with_laravel_backup_v9()
	{
		$this->listen('spatie-backup-events-v9.php');
		Bus::fake();

		$destination = \Spatie\Backup\BackupDestination\BackupDestination::create('s3', 'Clothing4U');

		event(new \Spatie\Backup\Events\BackupWasSuccessful($destination));
		event(new \Spatie\Backup\Events\BackupHasFailed(new Exception('S3 timeout'), $destination));

		Bus::assertDispatched(BackupReportJob::class, function (BackupReportJob $job) {
			return $job->event === ['type' => 'succeeded', 'disk' => 's3', 'name' => 'Clothing4U', 'error' => null];
		});

		Bus::assertDispatched(BackupReportJob::class, function (BackupReportJob $job) {
			return $job->event === ['type' => 'failed', 'disk' => 's3', 'name' => 'Clothing4U', 'error' => 'S3 timeout'];
		});
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	#[RunInSeparateProcess]
	#[PreserveGlobalState(false)]
	public function test_nothing_is_reported_when_turned_off()
	{
		$this->listen('spatie-backup-events-v10.php');
		config()->set('laravel-stats.backup-reporting-enabled', false);
		Bus::fake();

		event(new \Spatie\Backup\Events\BackupWasSuccessful('s3', 'Clothing4U'));

		Bus::assertNotDispatched(BackupReportJob::class);
	}

	public function test_without_laravel_backup_no_listeners_are_added()
	{
		if (class_exists(BackupSubscriber::SUCCEEDED)) {
			$this->markTestSkipped('The fake laravel-backup classes are loaded in this process.');
		}

		$this->assertFalse($this->app->make('events')->hasListeners(BackupSubscriber::SUCCEEDED));
	}
}
