<?php

namespace Mtvtd\LaravelStats\Tests\ShareableMetrics;

use Composer\InstalledVersions;
use Mtvtd\LaravelStats\Tests\TestCase;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics\Url;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics\Host;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics\Name;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics\PhpVersion;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics\ServerInfo;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics\Environment;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics\LaravelVersion;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics\InstalledVersion;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics\ManageLaravelTeam;

class SimpleMetricsTest extends TestCase
{
	public function test_host_metric_parses_host_from_configured_app_url()
	{
		config()->set('app.url', 'https://example.com/some/path');

		$metric = new Host();

		$this->assertSame('host', $metric->name());
		$this->assertSame('example.com', $metric->value());
	}

	public function test_environment_metric_returns_configured_environment()
	{
		config()->set('app.env', 'staging');

		$metric = new Environment();

		$this->assertSame('environment', $metric->name());
		$this->assertSame('staging', $metric->value());
	}

	public function test_name_metric_returns_configured_app_name()
	{
		config()->set('app.name', 'Acme');

		$metric = new Name();

		$this->assertSame('name', $metric->name());
		$this->assertSame('Acme', $metric->value());
	}

	public function test_url_metric_returns_root_url()
	{
		$metric = new Url();

		$this->assertSame('url', $metric->name());
		$this->assertSame(url('/'), $metric->value());
	}

	public function test_php_version_metric_matches_phpversion()
	{
		$metric = new PhpVersion();

		$this->assertSame('php-version', $metric->name());
		$this->assertSame(phpversion(), $metric->value());
	}

	public function test_laravel_version_metric_matches_application_version()
	{
		$metric = new LaravelVersion();

		$this->assertSame('laravel-version', $metric->name());
		$this->assertSame(app()->version(), $metric->value());
	}

	public function test_server_info_metric_returns_php_uname_breakdown()
	{
		$metric = new ServerInfo();
		$value = $metric->value();

		$this->assertSame('server-info', $metric->name());
		$this->assertIsArray($value);
		$this->assertSame(
			['os', 'hostname', 'release', 'version', 'machine'],
			array_keys($value)
		);
		$this->assertSame(php_uname('s'), $value['os']);
		$this->assertSame(php_uname('n'), $value['hostname']);
		$this->assertSame(php_uname('m'), $value['machine']);
	}

	public function test_installed_version_metric_returns_composer_installed_version()
	{
		$metric = new InstalledVersion();

		$this->assertSame('version', $metric->name());
		$this->assertSame(InstalledVersions::getVersion('mtvtd/laravel-stats'), $metric->value());
	}

	public function test_manage_laravel_team_metric_reads_team_id_config()
	{
		$metric = new ManageLaravelTeam();

		$this->assertSame('team_id', $metric->name());
		$this->assertNull($metric->value());

		config()->set('laravel-stats.team-id', 42);

		$this->assertSame(42, (new ManageLaravelTeam())->value());
	}

	public function test_metric_to_array_wraps_name_and_value()
	{
		config()->set('app.url', 'https://example.com');

		$this->assertSame(['host' => 'example.com'], (new Host())->toArray());
	}
}
