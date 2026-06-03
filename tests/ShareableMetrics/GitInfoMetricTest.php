<?php

namespace Mtvtd\LaravelStats\Tests\ShareableMetrics;

use Mtvtd\LaravelStats\Tests\TestCase;
use Mtvtd\LaravelStats\Helpers\SourceControl\GitInfo;
use Mtvtd\LaravelStats\Helpers\SourceControl\CommitInfo;
use Mtvtd\LaravelStats\Helpers\SourceControl\RemoteInfo;
use Mtvtd\LaravelStats\Helpers\SourceControl\GitInfoCollector;
use Mtvtd\LaravelStats\ShareableMetrics\Metrics\GitInfo as GitInfoMetric;

class GitInfoMetricTest extends TestCase
{
	public function test_value_returns_collector_payload_as_array()
	{
		$commit = (new CommitInfo())
			->setId('h')->setAuthorName('an')->setAuthorEmail('ae')
			->setCommitterName('cn')->setCommitterEmail('ce')
			->setMessage('msg')->setDate(42);

		$remote = (new RemoteInfo())->setName('origin')->setUrl('git@example.com:foo/bar.git');

		$gitInfo = new GitInfo('main', $commit, [$remote]);

		$this->app->instance(GitInfoCollector::class, new class($gitInfo) extends GitInfoCollector {
			private $payload;

			public function __construct(GitInfo $payload)
			{
				parent::__construct();
				$this->payload = $payload;
			}

			public function collect(): GitInfo
			{
				return $this->payload;
			}
		});

		$this->assertSame('git-info', (new GitInfoMetric())->name());
		$this->assertSame($gitInfo->toArray(), (new GitInfoMetric())->value());
	}
}
