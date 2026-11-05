<?php

namespace Mtvtd\LaravelStats\Tests\ShareableMetrics;

use Illuminate\Support\Facades\File;
use Mtvtd\LaravelStats\Tests\TestCase;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics\InstalledNpmPackages;

class InstalledNpmPackagesTest extends TestCase
{
	private function fake($packageJson, $lock = null): void
	{
		File::shouldReceive('get')->with(base_path('package.json'))->andReturnUsing(function () use ($packageJson) {
			return $packageJson === null ? $this->throwMissing() : json_encode($packageJson);
		});

		File::shouldReceive('get')->with(base_path('package-lock.json'))->andReturnUsing(function () use ($lock) {
			return $lock === null ? $this->throwMissing() : json_encode($lock);
		});
	}

	private function throwMissing(): void
	{
		throw new \Illuminate\Contracts\Filesystem\FileNotFoundException('missing');
	}

	public function test_it_splits_dependencies_and_dev_dependencies_and_reads_the_locked_versions()
	{
		$this->fake([
			'dependencies' => ['alpinejs' => '^3.14.0'],
			'devDependencies' => ['vite' => '^7.0.0', 'not-locked' => '^1.0.0'],
		], [
			'lockfileVersion' => 3,
			'packages' => [
				'' => ['name' => 'app'],
				'node_modules/alpinejs' => ['version' => '3.14.8'],
				'node_modules/vite' => ['version' => '7.1.2'],
			],
		]);

		$metric = new InstalledNpmPackages();

		$this->assertSame('npm-packages', $metric->name());
		$this->assertSame([
			['package' => 'alpinejs', 'constraint' => '^3.14.0', 'is_dev' => false, 'version' => '3.14.8'],
			['package' => 'vite', 'constraint' => '^7.0.0', 'is_dev' => true, 'version' => '7.1.2'],
			['package' => 'not-locked', 'constraint' => '^1.0.0', 'is_dev' => true, 'version' => null],
		], $metric->value());
	}

	public function test_it_reads_versions_from_a_version_1_lockfile()
	{
		$this->fake(['dependencies' => ['alpinejs' => '^3.0.0']], [
			'lockfileVersion' => 1,
			'dependencies' => ['alpinejs' => ['version' => '3.2.1']],
		]);

		$this->assertSame('3.2.1', (new InstalledNpmPackages())->value()[0]['version']);
	}

	public function test_it_reads_scoped_packages()
	{
		$this->fake(['dependencies' => ['@tailwindcss/vite' => '^4.0.0']], [
			'packages' => ['node_modules/@tailwindcss/vite' => ['version' => '4.1.0']],
		]);

		$this->assertSame('4.1.0', (new InstalledNpmPackages())->value()[0]['version']);
	}

	public function test_without_a_lockfile_the_versions_are_null()
	{
		$this->fake(['dependencies' => ['alpinejs' => '^3.0.0']]);

		$this->assertSame(
			[['package' => 'alpinejs', 'constraint' => '^3.0.0', 'is_dev' => false, 'version' => null]],
			(new InstalledNpmPackages())->value()
		);
	}

	public function test_a_package_json_without_dependencies_gives_an_empty_list()
	{
		$this->fake(['name' => 'app'], ['packages' => []]);

		$this->assertSame([], (new InstalledNpmPackages())->value());
	}

	public function test_without_a_package_json_the_value_is_null()
	{
		$this->fake(null);

		$this->assertNull((new InstalledNpmPackages())->value());
	}

	public function test_an_unreadable_package_json_gives_null()
	{
		File::shouldReceive('get')->with(base_path('package.json'))->andReturn('{not json');

		$this->assertNull((new InstalledNpmPackages())->value());
	}
}
