<?php

namespace Mtvtd\LaravelStats\Tests\ShareableMetrics;

use Illuminate\Support\Facades\File;
use Mtvtd\LaravelStats\Tests\TestCase;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics\InstalledPackages;

class InstalledPackagesTest extends TestCase
{
	public function test_it_splits_require_and_require_dev_and_resolves_versions()
	{
		$fixture = json_encode([
			'require' => [
				'php' => '^8.0',
				'spatie/laravel-package-tools' => '^1.4',
				'vendor/missing-package' => '^9.99',
			],
			'require-dev' => [
				'phpunit/phpunit' => '^9.0',
			],
		]);

		File::shouldReceive('get')
			->once()
			->with(base_path('composer.json'))
			->andReturn($fixture);

		$metric = new InstalledPackages();

		$this->assertSame('packages', $metric->name());

		$packages = $metric->value();

		$this->assertCount(4, $packages);

		$byName = [];

		foreach ($packages as $pkg) {
			$this->assertSame(['package', 'constraint', 'is_dev', 'version'], array_keys($pkg));
			$byName[$pkg['package']] = $pkg;
		}

		$this->assertSame(phpversion(), $byName['php']['version']);
		$this->assertFalse($byName['php']['is_dev']);

		$this->assertFalse($byName['spatie/laravel-package-tools']['is_dev']);
		$this->assertNotNull($byName['spatie/laravel-package-tools']['version']);

		$this->assertNull($byName['vendor/missing-package']['version']);

		$this->assertTrue($byName['phpunit/phpunit']['is_dev']);
		$this->assertNotNull($byName['phpunit/phpunit']['version']);
	}

	public function test_it_handles_a_composer_json_with_no_packages()
	{
		File::shouldReceive('get')
			->once()
			->with(base_path('composer.json'))
			->andReturn(json_encode(new \stdClass()));

		$this->assertSame([], (new InstalledPackages())->value());
	}
}
