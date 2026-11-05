<?php

namespace Mtvtd\LaravelStats\Tests\ShareableMetrics;

use Illuminate\Support\Facades\File;
use Mtvtd\LaravelStats\Tests\TestCase;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics\Dependencies;

class DependenciesTest extends TestCase
{
	private function fake(array $composerJson, $installed): void
	{
		File::shouldReceive('get')->with(base_path('composer.json'))->andReturn(json_encode($composerJson));
		File::shouldReceive('get')->with(base_path('vendor/composer/installed.json'))->andReturn(json_encode($installed));
	}

	private function installed(): array
	{
		return [
			'packages' => [
				['name' => 'laravel/framework', 'version' => 'v12.69.3', 'version_normalized' => '12.69.3.0', 'require' => ['php' => '^8.2', 'league/commonmark' => '^2.7', 'guzzlehttp/guzzle' => '^7.8'], 'replace' => ['illuminate/support' => 'self.version']],
				['name' => 'spatie/laravel-backup', 'version' => '9.3.0', 'version_normalized' => '9.3.0.0', 'require' => ['illuminate/support' => '^12.0', 'guzzlehttp/guzzle' => '^7.0']],
				['name' => 'league/commonmark', 'version' => '2.7.0', 'version_normalized' => '2.7.0.0', 'require' => ['league/config' => '^1.1', 'ext-mbstring' => '*']],
				['name' => 'league/config', 'version' => 'v1.2.0', 'version_normalized' => '1.2.0.0'],
				['name' => 'guzzlehttp/guzzle', 'version' => '7.9.2', 'version_normalized' => '7.9.2.0', 'require' => ['guzzlehttp/psr7' => '^2.7']],
				['name' => 'guzzlehttp/psr7', 'version' => '2.4.0', 'version_normalized' => '2.4.0.0'],
				['name' => 'phpunit/phpunit', 'version' => '11.5.0', 'version_normalized' => '11.5.0.0', 'require' => ['sebastian/diff' => '^6.0']],
				['name' => 'sebastian/diff', 'version' => '6.0.2', 'version_normalized' => '6.0.2.0'],
			],
			'dev' => true,
			'dev-package-names' => ['phpunit/phpunit', 'sebastian/diff'],
		];
	}

	public function test_it_lists_the_packages_that_come_along_and_which_packages_pull_them_in()
	{
		$this->fake([
			'require' => ['php' => '^8.2', 'laravel/framework' => '^12.0', 'spatie/laravel-backup' => '^9.0'],
			'require-dev' => ['phpunit/phpunit' => '^11.0'],
		], $this->installed());

		$metric = new Dependencies();
		$this->assertSame('dependencies', $metric->name());

		$byName = collect($metric->value())->keyBy('package');

		// The packages in composer.json are sent as "packages", not again here.
		$this->assertFalse($byName->has('laravel/framework'));
		$this->assertFalse($byName->has('phpunit/phpunit'));

		$this->assertSame(['league/commonmark', 'league/config', 'guzzlehttp/guzzle', 'guzzlehttp/psr7', 'sebastian/diff'], $byName->keys()->sortBy(fn ($name) => array_search($name, ['league/commonmark', 'league/config', 'guzzlehttp/guzzle', 'guzzlehttp/psr7', 'sebastian/diff']))->values()->all());

		$this->assertSame(['package' => 'guzzlehttp/psr7', 'version' => '2.4.0.0', 'is_dev' => false, 'via' => ['laravel/framework', 'spatie/laravel-backup']], $byName['guzzlehttp/psr7']);
		$this->assertSame(['laravel/framework'], $byName['league/config']['via']);
		$this->assertSame(['phpunit/phpunit'], $byName['sebastian/diff']['via']);
		$this->assertTrue($byName['sebastian/diff']['is_dev']);
	}

	public function test_a_requirement_on_a_replaced_package_leads_to_the_package_that_replaces_it()
	{
		// spatie/laravel-backup requires illuminate/support, which laravel/framework replaces.
		$this->fake(['require' => ['spatie/laravel-backup' => '^9.0']], $this->installed());

		$byName = collect((new Dependencies())->value())->keyBy('package');

		$this->assertSame(['spatie/laravel-backup'], $byName['laravel/framework']['via']);
		$this->assertSame(['spatie/laravel-backup'], $byName['league/commonmark']['via']);
	}

	public function test_it_reads_the_composer_1_format()
	{
		$this->fake(['require' => ['guzzlehttp/guzzle' => '^7.0']], $this->installed()['packages']);

		$byName = collect((new Dependencies())->value())->keyBy('package');

		$this->assertSame(['guzzlehttp/guzzle'], $byName['guzzlehttp/psr7']['via']);
		$this->assertFalse($byName['guzzlehttp/psr7']['is_dev']);
	}

	public function test_nothing_is_sent_without_an_installed_json()
	{
		File::shouldReceive('get')->with(base_path('composer.json'))->andReturn('{}');
		File::shouldReceive('get')->with(base_path('vendor/composer/installed.json'))->andThrow(new \Illuminate\Contracts\Filesystem\FileNotFoundException());

		$this->assertNull((new Dependencies())->value());
	}
}
