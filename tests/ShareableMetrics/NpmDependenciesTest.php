<?php

namespace Mtvtd\LaravelStats\Tests\ShareableMetrics;

use Illuminate\Support\Facades\File;
use Mtvtd\LaravelStats\Tests\TestCase;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics\NpmDependencies;

class NpmDependenciesTest extends TestCase
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

	public function test_it_reports_the_sub_packages_with_the_packages_that_pull_them_in()
	{
		$this->fake([
			'dependencies' => ['axios' => '^1.7.0'],
			'devDependencies' => ['vite' => '^7.0.0'],
		], [
			'lockfileVersion' => 3,
			'packages' => [
				'' => ['name' => 'app', 'dependencies' => ['axios' => '^1.7.0']],
				'node_modules/axios' => ['version' => '1.7.9', 'dependencies' => ['follow-redirects' => '^1.15.6', 'form-data' => '^4.0.0']],
				'node_modules/follow-redirects' => ['version' => '1.15.9'],
				'node_modules/form-data' => ['version' => '4.0.1', 'dependencies' => ['mime-types' => '^2.1.12']],
				'node_modules/mime-types' => ['version' => '2.1.35'],
				'node_modules/vite' => ['version' => '7.1.2', 'dev' => true, 'dependencies' => ['esbuild' => '^0.25.0'], 'peerDependencies' => ['@types/node' => '*']],
				'node_modules/esbuild' => ['version' => '0.25.4', 'dev' => true],
			],
		]);

		$metric = new NpmDependencies();

		$this->assertSame('npm-dependencies', $metric->name());
		$this->assertSame([
			['package' => 'esbuild', 'version' => '0.25.4', 'is_dev' => true, 'via' => ['vite']],
			['package' => 'follow-redirects', 'version' => '1.15.9', 'is_dev' => false, 'via' => ['axios']],
			['package' => 'form-data', 'version' => '4.0.1', 'is_dev' => false, 'via' => ['axios']],
			['package' => 'mime-types', 'version' => '2.1.35', 'is_dev' => false, 'via' => ['axios']],
		], $metric->value());
	}

	public function test_a_nested_copy_is_resolved_before_the_top_level_one()
	{
		$this->fake(['dependencies' => ['a' => '*', 'b' => '*']], [
			'packages' => [
				'node_modules/a' => ['version' => '1.0.0', 'dependencies' => ['shared' => '^2.0.0']],
				'node_modules/a/node_modules/shared' => ['version' => '2.0.0'],
				'node_modules/b' => ['version' => '1.0.0', 'dependencies' => ['shared' => '^1.0.0']],
				'node_modules/shared' => ['version' => '1.5.0'],
			],
		]);

		$this->assertSame([
			['package' => 'shared', 'version' => '1.5.0', 'is_dev' => false, 'via' => ['b']],
			['package' => 'shared', 'version' => '2.0.0', 'is_dev' => false, 'via' => ['a']],
		], (new NpmDependencies())->value());
	}

	public function test_it_stops_at_the_other_package_json_packages()
	{
		// laravel-vite-plugin needs vite, but vite is in package.json itself: esbuild is vite's, not the plugin's.
		$this->fake(['devDependencies' => ['laravel-vite-plugin' => '*', 'vite' => '*']], [
			'packages' => [
				'node_modules/laravel-vite-plugin' => ['version' => '2.0.0', 'dev' => true, 'peerDependencies' => ['vite' => '*']],
				'node_modules/vite' => ['version' => '7.1.2', 'dev' => true, 'dependencies' => ['esbuild' => '*']],
				'node_modules/esbuild' => ['version' => '0.25.4', 'dev' => true],
			],
		]);

		$this->assertSame(
			[['package' => 'esbuild', 'version' => '0.25.4', 'is_dev' => true, 'via' => ['vite']]],
			(new NpmDependencies())->value()
		);
	}

	public function test_scoped_packages_and_shared_sub_packages()
	{
		$this->fake(['dependencies' => ['@tailwindcss/vite' => '*', 'tailwindcss' => '*', 'axios' => '*']], [
			'packages' => [
				'node_modules/@tailwindcss/vite' => ['version' => '4.1.0', 'dependencies' => ['@tailwindcss/node' => '*']],
				'node_modules/@tailwindcss/node' => ['version' => '4.1.0', 'dependencies' => ['jiti' => '*']],
				'node_modules/jiti' => ['version' => '2.4.2'],
				'node_modules/tailwindcss' => ['version' => '4.1.0'],
				'node_modules/axios' => ['version' => '1.7.9', 'dependencies' => ['jiti' => '*']],
			],
		]);

		$this->assertSame([
			['package' => '@tailwindcss/node', 'version' => '4.1.0', 'is_dev' => false, 'via' => ['@tailwindcss/vite']],
			['package' => 'jiti', 'version' => '2.4.2', 'is_dev' => false, 'via' => ['@tailwindcss/vite', 'axios']],
		], (new NpmDependencies())->value());
	}

	public function test_workspaces_and_links_are_skipped()
	{
		$this->fake(['dependencies' => ['ui' => '*']], [
			'packages' => [
				'packages/ui' => ['name' => 'ui', 'version' => '0.0.1'],
				'node_modules/ui' => ['resolved' => 'packages/ui', 'link' => true],
			],
		]);

		$this->assertSame([], (new NpmDependencies())->value());
	}

	public function test_a_version_1_lockfile_gives_null()
	{
		$this->fake(['dependencies' => ['axios' => '*']], [
			'lockfileVersion' => 1,
			'dependencies' => ['axios' => ['version' => '0.21.1']],
		]);

		$this->assertNull((new NpmDependencies())->value());
	}

	public function test_without_a_lockfile_the_value_is_null()
	{
		$this->fake(['dependencies' => ['axios' => '*']]);

		$this->assertNull((new NpmDependencies())->value());
	}

	public function test_without_a_package_json_the_value_is_null()
	{
		$this->fake(null, ['packages' => []]);

		$this->assertNull((new NpmDependencies())->value());
	}
}
