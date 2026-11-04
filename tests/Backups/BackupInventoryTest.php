<?php

namespace Mtvtd\LaravelStats\Tests\Backups;

use Illuminate\Support\Carbon;
use Mtvtd\LaravelStats\Tests\TestCase;
use Spatie\Backup\BackupDestination\Backup;
use Mtvtd\LaravelStats\Backups\BackupInventory;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Spatie\Backup\BackupDestination\BackupDestination;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics\Backups;

class BackupInventoryTest extends TestCase
{
	private function loadFakes(): void
	{
		require_once __DIR__ . '/../Fakes/spatie-backup-destination.php';

		config()->set('backup.backup.name', 'Clothing4U');
		config()->set('backup.backup.destination.disks', ['s3', 'local']);
	}

	public function test_without_laravel_backup_nothing_is_sent()
	{
		if (class_exists(BackupInventory::DESTINATION_CLASS)) {
			$this->markTestSkipped('The fake laravel-backup classes are loaded in this process.');
		}

		$this->assertFalse(app(BackupInventory::class)->available());
		$this->assertSame(['backups' => null], (new Backups())->toArray());
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	#[RunInSeparateProcess]
	#[PreserveGlobalState(false)]
	public function test_it_lists_the_backups_of_every_destination()
	{
		$this->loadFakes();

		BackupDestination::$disks = [
			's3' => ['reachable' => true, 'backups' => [
				new Backup('Clothing4U/2026-11-04-02-00-03.zip', Carbon::parse('2026-11-04 02:00:03'), 1500),
				new Backup('Clothing4U/2026-11-03-02-00-03.zip', Carbon::parse('2026-11-03 02:00:03'), 1400),
			]],
			'local' => ['reachable' => false, 'error' => 'Permission denied'],
		];

		$destinations = app(BackupInventory::class)->destinations();

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

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	#[RunInSeparateProcess]
	#[PreserveGlobalState(false)]
	public function test_a_broken_disk_does_not_stop_the_others()
	{
		$this->loadFakes();

		BackupDestination::$disks = [
			's3' => ['throws' => true],
			'local' => ['reachable' => true, 'backups' => [new Backup('Clothing4U/a.zip', Carbon::now(), 10)]],
		];

		$destinations = app(BackupInventory::class)->destinations();

		$this->assertFalse($destinations[0]['reachable']);
		$this->assertStringContainsString('does not have a configured driver', $destinations[0]['error']);
		$this->assertCount(1, $destinations[1]['backups']);
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	#[RunInSeparateProcess]
	#[PreserveGlobalState(false)]
	public function test_only_the_newest_backups_are_sent()
	{
		$this->loadFakes();
		config()->set('backup.backup.destination.disks', ['s3']);
		config()->set('laravel-stats.backups.max', 1);

		BackupDestination::$disks = ['s3' => ['reachable' => true, 'backups' => [
			new Backup('Clothing4U/new.zip', Carbon::now(), 10),
			new Backup('Clothing4U/old.zip', Carbon::now()->subDay(), 10),
		]]];

		$this->assertSame(['Clothing4U/new.zip'], array_column(app(BackupInventory::class)->destinations()[0]['backups'], 'path'));
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	#[RunInSeparateProcess]
	#[PreserveGlobalState(false)]
	public function test_reporting_can_be_turned_off()
	{
		$this->loadFakes();
		config()->set('laravel-stats.backup-reporting-enabled', false);

		$this->assertSame(['backups' => null], (new Backups())->toArray());
	}
}
