<?php

namespace Mtvtd\LaravelStats\ShareableMetrics\Metrics;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Mtvtd\LaravelStats\ShareableMetrics\Metric;
use Mtvtd\LaravelStats\ShareableMetrics\CollectableMetric;

/**
 * Every installed Composer package that is not in composer.json itself (the packages that come along with them),
 * with the composer.json packages that pull it in. Lets the dashboard find vulnerabilities in those as well.
 */
class Dependencies extends Metric implements CollectableMetric
{
	public function name(): string
	{
		return 'dependencies';
	}

	public function value()
	{
		try {
			$composerJson = json_decode(File::get(base_path('composer.json')), true);
			$installed = json_decode(File::get(base_path('vendor/composer/installed.json')), true);
		} catch (\Throwable $exception) {
			return null;
		}

		if ( ! is_array($composerJson) || ! is_array($installed)) {
			return null;
		}

		// Composer 2 wraps the list, Composer 1 is the list itself.
		$packages = collect($installed['packages'] ?? $installed)
			->filter(function ($package) {
				return is_array($package) && isset($package['name']);
			})
			->keyBy(function (array $package) {
				return strtolower($package['name']);
			});

		$devNames = array_map('strtolower', $installed['dev-package-names'] ?? []);

		// laravel/framework replaces illuminate/*: a requirement on one of those leads to the package that replaces it.
		$aliases = [];

		foreach ($packages as $name => $package) {
			foreach (array_keys(($package['replace'] ?? []) + ($package['provide'] ?? [])) as $alias) {
				$aliases[strtolower($alias)] = $name;
			}
		}

		$resolve = function (string $name) use ($packages, $aliases) {
			$name = strtolower($name);

			return $packages->has($name) ? $name : ($aliases[$name] ?? null);
		};

		$roots = collect(array_keys(Arr::get($composerJson, 'require', []) + Arr::get($composerJson, 'require-dev', [])))
			->map($resolve)
			->filter()
			->unique()
			->values();

		$via = [];

		foreach ($roots as $root) {
			$stack = [$root];

			// Stop at the other composer.json packages: what they pull in is theirs, not this package's.
			$seen = $roots->flip()->map(function () {
				return true;
			})->all();

			while ($stack) {
				$current = array_pop($stack);

				foreach (array_keys($packages[$current]['require'] ?? []) as $requirement) {
					$dependency = $resolve($requirement);

					if ($dependency === null || isset($seen[$dependency])) {
						continue;
					}

					$seen[$dependency] = true;
					$via[$dependency][] = $packages[$root]['name'];
					$stack[] = $dependency;
				}
			}
		}

		return $packages
			->reject(function (array $package, string $name) use ($roots) {
				return $roots->contains($name);
			})
			->map(function (array $package, string $name) use ($devNames, $via) {
				$parents = array_values(array_unique($via[$name] ?? []));
				sort($parents);

				return [
					'package' => $package['name'],
					'version' => $package['version_normalized'] ?? $package['version'] ?? null,
					'is_dev' => in_array($name, $devNames, true),
					'via' => $parents,
				];
			})
			->sortBy('package')
			->values()
			->toArray();
	}
}
