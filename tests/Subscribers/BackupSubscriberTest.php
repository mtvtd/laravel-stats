<?php

namespace Mtvtd\LaravelStats\Tests\Subscribers;

use Exception;
use Illuminate\Support\Facades\Bus;
use Mtvtd\LaravelStats\Tests\TestCase;
use Mtvtd\LaravelStats\Jobs\BackupReportJob;
use Mtvtd\LaravelStats\Subscribers\BackupSubscriber;
use Mtvtd\LaravelStats\Tests\Fakes\FakeBackupEvents;
use Mtvtd\LaravelStats\Tests\Fakes\FakeBackupDestination;

class BackupSubscriberTest extends TestCase
{
	private function assertReported(array $event): void
	{
		Bus::assertDispatched(BackupReportJob::class, function (BackupReportJob $job) use ($event) {
			return $job->event === $event;
		});
	}

	public function test_a_successful_backup_is_reported_with_laravel_backup_v10()
	{
		Bus::fake();

		(new BackupSubscriber())->handle(FakeBackupEvents::succeededV10('s3', 'Clothing4U'));

		$this->assertReported(['type' => 'succeeded', 'disk' => 's3', 'name' => 'Clothing4U', 'error' => null]);
	}

	public function test_a_failed_backup_is_reported_with_its_error_with_laravel_backup_v10()
	{
		Bus::fake();

		(new BackupSubscriber())->handle(FakeBackupEvents::failedV10(new Exception('mysqldump: Access denied')));

		$this->assertReported(['type' => 'failed', 'disk' => null, 'name' => null, 'error' => 'mysqldump: Access denied']);
	}

	public function test_backups_are_reported_with_laravel_backup_v9()
	{
		Bus::fake();

		$destination = FakeBackupDestination::create('s3', 'Clothing4U');

		(new BackupSubscriber())->handle(FakeBackupEvents::succeededV9($destination));
		(new BackupSubscriber())->handle(FakeBackupEvents::failedV9(new Exception('S3 timeout'), $destination));
		(new BackupSubscriber())->handle(FakeBackupEvents::failedV9(new Exception('Dump failed')));

		$this->assertReported(['type' => 'succeeded', 'disk' => 's3', 'name' => 'Clothing4U', 'error' => null]);
		$this->assertReported(['type' => 'failed', 'disk' => 's3', 'name' => 'Clothing4U', 'error' => 'S3 timeout']);
		$this->assertReported(['type' => 'failed', 'disk' => null, 'name' => null, 'error' => 'Dump failed']);
	}

	public function test_nothing_is_reported_when_turned_off()
	{
		config()->set('laravel-stats.backup-reporting-enabled', false);
		Bus::fake();

		(new BackupSubscriber())->handle(FakeBackupEvents::succeededV10('s3', 'Clothing4U'));

		Bus::assertNotDispatched(BackupReportJob::class);
	}

	public function test_without_laravel_backup_no_listeners_are_added()
	{
		if (class_exists(BackupSubscriber::SUCCEEDED)) {
			$this->markTestSkipped('spatie/laravel-backup is installed.');
		}

		$this->assertFalse($this->app->make('events')->hasListeners(BackupSubscriber::SUCCEEDED));
	}
}
