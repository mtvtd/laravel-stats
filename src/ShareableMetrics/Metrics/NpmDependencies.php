<?php

namespace Mtvtd\LaravelStats\ShareableMetrics\Metrics;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Mtvtd\LaravelStats\ShareableMetrics\Metric;
use Mtvtd\LaravelStats\ShareableMetrics\CollectableMetric;

/**
 * Every NPM package in package-lock.json that is not in package.json itself (the packages that come along with them),
 * with the package.json packages that pull it in. Lets the dashboard find vulnerabilities in those as well.
 *
 * Reads lockfile v2 and v3 (npm 7 and newer). Null without a readable package.json and lockfile, or with a v1 lockfile,
 * so the dashboard keeps what it already has.
 */
class NpmDependencies extends Metric implements CollectableMetric
{
	private const PREFIX = 'node_modules/';

	public function name(): string
	{
		return 'npm-dependencies';
	}

	public function value()
	{
		$packageJson = $this->read('package.json');
		$lock = $this->read('package-lock.json');

		if ( ! is_array($packageJson) || ! is_array($lock) || ! is_array($lock['packages'] ?? null)) {
			return null;
		}

		// Installed packages only: no workspace folders and no links to them.
		$packages = collect($lock['packages'])->filter(function ($package, $path) {
			return is_array($package) && strpos($path, self::PREFIX) === 0 && empty($package['link']);
		});

		$roots = collect(array_keys(
			Arr::get($packageJson, 'dependencies', [])
			+ Arr::get($packageJson, 'devDependencies', [])
			+ Arr::get($packageJson, 'optionalDependencies', [])
		))->mapWithKeys(function (string $name) {
			return [self::PREFIX . $name => $name];
		})->filter(function (string $name, string $path) use ($packages) {
			return $packages->has($path);
		});

		$via = [];

		foreach ($roots as $rootPath => $rootName) {
			$stack = [$rootPath];

			// Stop at the other package.json packages: what they pull in is theirs, not this package's.
			$seen = $roots->map(function () {
				return true;
			})->all();

			while ($stack) {
				$current = array_pop($stack);
				$package = $packages[$current];

				$requirements = array_keys(
					($package['dependencies'] ?? [])
					+ ($package['optionalDependencies'] ?? [])
					+ ($package['peerDependencies'] ?? [])
				);

				foreach ($requirements as $requirement) {
					$dependency = $this->resolve($packages->all(), $current, $requirement);

					if ($dependency === null || isset($seen[$dependency])) {
						continue;
					}

					$seen[$dependency] = true;
					$via[$dependency][] = $rootName;
					$stack[] = $dependency;
				}
			}
		}

		// The same package can be installed in several versions (nested node_modules): one entry per version.
		return $packages
			->reject(function (array $package, string $path) use ($roots) {
				return $roots->has($path);
			})
			->map(function (array $package, string $path) use ($via) {
				return [
					'package' => $package['name'] ?? substr($path, strrpos($path, self::PREFIX) + strlen(self::PREFIX)),
					'version' => $package['version'] ?? null,
					'is_dev' => ! empty($package['dev']),
					'via' => $via[$path] ?? [],
				];
			})
			->groupBy(function (array $entry) {
				return $entry['package'] . '@' . $entry['version'];
			})
			->map(function ($entries) {
				$parents = $entries->pluck('via')->flatten()->unique()->sort()->values()->all();

				return [
					'package' => $entries->first()['package'],
					'version' => $entries->first()['version'],
					// Only dev when every copy is: one production copy makes it ship.
					'is_dev' => $entries->every(function (array $entry) {
						return $entry['is_dev'];
					}),
					'via' => $parents,
				];
			})
			->sortBy(function (array $entry) {
				return $entry['package'] . '@' . $entry['version'];
			})
			->values()
			->toArray();
	}

	/**
	 * Node's lookup: the package's own node_modules first, then every parent's, up to the top-level node_modules.
	 *
	 * @param array<string, array> $packages keyed by lockfile path
	 */
	protected function resolve(array $packages, string $from, string $name): ?string
	{
		$base = $from;

		while (true) {
			$candidate = $base . '/' . self::PREFIX . $name;

			if (isset($packages[$candidate])) {
				return $candidate;
			}

			$parent = strrpos($base, '/' . self::PREFIX);

			if ($parent === false) {
				return isset($packages[self::PREFIX . $name]) ? self::PREFIX . $name : null;
			}

			$base = substr($base, 0, $parent);
		}
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
