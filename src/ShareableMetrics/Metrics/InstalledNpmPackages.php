<?php

namespace Mtvtd\LaravelStats\ShareableMetrics\Metrics;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Mtvtd\LaravelStats\ShareableMetrics\Metric;
use Mtvtd\LaravelStats\ShareableMetrics\CollectableMetric;

/**
 * The NPM packages in package.json (dependencies and devDependencies), with the version that package-lock.json pins.
 * Null when the project has no readable package.json, so the dashboard keeps what it already has.
 */
class InstalledNpmPackages extends Metric implements CollectableMetric
{
	public function name(): string
	{
		return 'npm-packages';
	}

	public function value()
	{
		$packageJson = $this->read('package.json');

		if ( ! is_array($packageJson)) {
			return null;
		}

		$lock = $this->read('package-lock.json') ?: [];

		$packages = collect(Arr::get($packageJson, 'dependencies', []))
			->map(function ($constraint, $package) use ($lock) {
				return $this->entry($package, $constraint, false, $lock);
			});

		$devPackages = collect(Arr::get($packageJson, 'devDependencies', []))
			->map(function ($constraint, $package) use ($lock) {
				return $this->entry($package, $constraint, true, $lock);
			});

		return $packages->merge($devPackages)->values()->toArray();
	}

	protected function entry(string $package, $constraint, bool $isDev, array $lock): array
	{
		return [
			'package' => $package,
			'constraint' => $constraint,
			'is_dev' => $isDev,
			'version' => $this->getVersion($package, $lock),
		];
	}

	/**
	 * Lockfile v2 and v3 list every package under "packages", lockfile v1 under "dependencies".
	 */
	protected function getVersion(string $package, array $lock): ?string
	{
		return Arr::get($lock, 'packages.node_modules/' . $package . '.version')
			?? Arr::get($lock, 'dependencies.' . $package . '.version');
	}

	protected function read(string $file): ?array
	{
		try {
			$contents = json_decode(File::get(base_path($file)), true);
		} catch (\Throwable $exception) {
			return null;
		}

		return is_array($contents) ? $contents : null;
	}
}
