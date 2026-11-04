<?php

namespace Mtvtd\LaravelStats\Tests\Backups;

use Illuminate\Support\Carbon;
use Mtvtd\LaravelStats\Tests\TestCase;
use Mtvtd\LaravelStats\Tests\Fakes\FakeBackup;
use Mtvtd\LaravelStats\Backups\BackupInventory;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics\Backups;
use Mtvtd\LaravelStats\Tests\Fakes\FakeBackupDestination;

class BackupInventoryTest extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		FakeBackupDestination::$disks = [];

		config()->set('backup.backup.name', 'Clothing4U');
		config()->set('backup.backup.destination.disks', ['s3', 'local']);
	}

	private function inventory(): BackupInventory
	{
		return new BackupInventory(FakeBackupDestination::class);
	}

	public function test_without_laravel_backup_nothing_is_sent()
	{
		$this->assertFalse((new BackupInventory('Spatie\Backup\DoesNotExist'))->available());
		$this->assertSame([], (new BackupInventory('Spatie\Backup\DoesNotExist'))->destinations());

		if ( ! class_exists(BackupInventory::DESTINATION_CLASS)) {
			$this->assertSame(['backups' => null], (new Backups())->toArray());
		}
	}

	public function test_without_a_backup_name_nothing_is_sent()
	{
		config()->set('backup.backup.name', null);

		$this->assertFalse($this->inventory()->available());
	}

	public function test_it_lists_the_backups_of_every_destination()
	{
		FakeBackupDestination::$disks = [
			's3' => ['reachable' => true, 'backups' => [
				new FakeBackup('Clothing4U/2026-11-04-02-00-03.zip', Carbon::parse('2026-11-04 02:00:03'), 1500),
				new FakeBackup('Clothing4U/2026-11-03-02-00-03.zip', Carbon::parse('2026-11-03 02:00:03'), 1400),
			]],
			'local' => ['reachable' => false, 'error' => 'Permission denied'],
		];

		$destinations = $this->inventory()->destinations();

		$this->assertCount(2, $destinations);

		$this->assertSame('s3', $destinations[0]['disk']);
		$this->assertSame('Clothing4U', $destinations[0]['name']);
		$this->assertTrue($destinations[0]['reachable']);
		$this->assertSame(2900.0, $destinations[0]['used_storage']);
		$this->assertSame('Clothing4U/2026-11-04-02-00-03.zip', $destinations[0]['backups'][0]['path']);
		$this->assertSame(1500.0, $destinations[0]['backups'][0]['size']);
		$this->assertSame(Carbon::parse('2026-11-04 02:00:03')->toIso8601String(), $destinations[0]['backups'][0]['date']);

		$this->assertFalse($destinations[1]['reachable']);
		$this->assertSame('Permission denied', $destinations[1]['error']);
		$this->assertSame([], $destinations[1]['backups']);
	}

	public function test_a_broken_disk_does_not_stop_the_others()
	{
		FakeBackupDestination::$disks = [
			's3' => ['throws' => true],
			'local' => ['reachable' => true, 'backups' => [new FakeBackup('Clothing4U/a.zip', Carbon::now(), 10)]],
		];

		$destinations = $this->inventory()->destinations();

		$this->assertFalse($destinations[0]['reachable']);
		$this->assertStringContainsString('does not have a configured driver', $destinations[0]['error']);
		$this->assertCount(1, $destinations[1]['backups']);
	}

	public function test_only_the_newest_backups_are_sent()
	{
		config()->set('backup.backup.destination.disks', ['s3']);
		config()->set('laravel-stats.backups.max', 1);

		FakeBackupDestination::$disks = ['s3' => ['reachable' => true, 'backups' => [
			new FakeBackup('Clothing4U/new.zip', Carbon::now(), 10),
			new FakeBackup('Clothing4U/old.zip', Carbon::now()->subDay(), 10),
		]]];

		$this->assertSame(['Clothing4U/new.zip'], array_column($this->inventory()->destinations()[0]['backups'], 'path'));
	}

	public function test_the_metric_sends_the_destinations()
	{
		config()->set('backup.backup.destination.disks', ['s3']);
		$this->app->instance(BackupInventory::class, $this->inventory());

		$this->assertSame('s3', (new Backups())->toArray()['backups'][0]['disk']);
	}

	public function test_reporting_can_be_turned_off()
	{
		$this->app->instance(BackupInventory::class, $this->inventory());
		config()->set('laravel-stats.backup-reporting-enabled', false);

		$this->assertSame(['backups' => null], (new Backups())->toArray());
	}
}
