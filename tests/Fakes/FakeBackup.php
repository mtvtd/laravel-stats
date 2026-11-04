<?php

namespace Mtvtd\LaravelStats\Tests\Fakes;

use Illuminate\Support\Carbon;

/**
 * Stands in for Spatie\Backup\BackupDestination\Backup.
 */
class FakeBackup
{
	private string $path;

	private Carbon $date;

	private float $size;

	public function __construct(string $path, Carbon $date, float $size)
	{
		$this->path = $path;
		$this->date = $date;
		$this->size = $size;
	}

	public function path(): string
	{
		return $this->path;
	}

	public function date(): Carbon
	{
		return $this->date;
	}

	public function sizeInBytes(): float
	{
		return $this->size;
	}
}
