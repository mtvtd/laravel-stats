<?php

namespace Spaanproductions\ManageLaravelStats\ShareableMetrics\Metrics;

use ReflectionExtension;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Composer\InstalledVersions;
use Illuminate\Support\Facades\File;
use Spaanproductions\ManageLaravelStats\ShareableMetrics\Metric;
use Spaanproductions\ManageLaravelStats\ShareableMetrics\CollectableMetric;

class InstalledPackages extends Metric implements CollectableMetric
{
	public function name(): string
	{
		return 'packages';
	}

	public function value()
	{
		$composerJson = json_decode(File::get(base_path('composer.json')), true);

		$packages = collect(Arr::get($composerJson, 'require', []))
			->map(function ($constraint, $package) {
				return [
					'package' => $package,
					'constraint' => $constraint,
					'is_dev' => false,
					'version' => $this->getVersion($package),
				];
			});

		$devPackages = collect(Arr::get($composerJson, 'require-dev', []))
			->map(function ($constraint, $package) {
				return [
					'package' => $package,
					'constraint' => $constraint,
					'is_dev' => true,
					'version' => $this->getVersion($package),
				];
			});


		return $packages->merge($devPackages)->toArray();
	}

	protected function getVersion($package)
	{
		if ($package === 'php') {
			return phpversion();
		}

		if (Str::startsWith($package, 'ext-')) {
			try {
				return (new ReflectionExtension(str_replace('ext-', '', $package)))
					->getVersion();
			} catch (\Exception $exception) {
				//
			}
		}

		return InstalledVersions::isInstalled($package) ? InstalledVersions::getVersion($package) : null;
	}
}
