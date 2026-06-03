<?php

namespace Mtvtd\LaravelStats\Tests\Helpers\SourceControl;

use Mtvtd\LaravelStats\Tests\TestCase;
use Mtvtd\LaravelStats\Helpers\SystemCommandExecutor;
use Mtvtd\LaravelStats\Helpers\SourceControl\GitInfoCollector;

class GitInfoCollectorTest extends TestCase
{
	public function test_collect_returns_branch_commit_and_remotes()
	{
		$executor = new FakeSystemCommandExecutor([
			'git branch' => ['  feature/x', '* main', '  other'],
			'git log -1 --pretty=format:%H%n%aN%n%ae%n%cN%n%ce%n%s%n%at' => [
				'abc123',
				'Ada Lovelace',
				'ada@example.com',
				'Grace Hopper',
				'grace@example.com',
				'Initial commit',
				'1717000000',
			],
			'git remote -v' => [
				"origin\tgit@github.com:foo/bar.git (fetch)",
				"origin\tgit@github.com:foo/bar.git (push)",
				"mirror\thttps://example.com/foo.git (fetch)",
				"mirror\thttps://example.com/foo.git (push)",
			],
		]);

		$info = (new GitInfoCollector($executor))->collect();

		$this->assertSame('main', $info->getBranch());
		$this->assertSame('abc123', $info->getHead()->getId());
		$this->assertSame('Ada Lovelace', $info->getHead()->getAuthorName());
		$this->assertSame('ada@example.com', $info->getHead()->getAuthorEmail());
		$this->assertSame('Grace Hopper', $info->getHead()->getCommitterName());
		$this->assertSame('grace@example.com', $info->getHead()->getCommitterEmail());
		$this->assertSame('Initial commit', $info->getHead()->getMessage());
		$this->assertSame(1717000000, $info->getHead()->getDate());

		$remotes = $info->getRemotes();
		$this->assertCount(2, $remotes);
		$this->assertSame('origin', $remotes[0]->getName());
		$this->assertSame('git@github.com:foo/bar.git', $remotes[0]->getUrl());
		$this->assertSame('mirror', $remotes[1]->getName());
		$this->assertSame('https://example.com/foo.git', $remotes[1]->getUrl());
	}

	public function test_collect_handles_empty_remote_list()
	{
		$executor = new FakeSystemCommandExecutor([
			'git branch' => ['* main'],
			'git log -1 --pretty=format:%H%n%aN%n%ae%n%cN%n%ce%n%s%n%at' => [
				'h', 'an', 'ae', 'cn', 'ce', 'msg', '0',
			],
			'git remote -v' => [],
		]);

		$info = (new GitInfoCollector($executor))->collect();

		$this->assertSame([], $info->getRemotes());
	}

	public function test_collect_throws_when_log_output_has_wrong_line_count()
	{
		$executor = new FakeSystemCommandExecutor([
			'git branch' => ['* main'],
			'git log -1 --pretty=format:%H%n%aN%n%ae%n%cN%n%ce%n%s%n%at' => ['only', 'six', 'of', 'seven', 'lines', 'here'],
			'git remote -v' => [],
		]);

		$this->expectException(\RuntimeException::class);

		(new GitInfoCollector($executor))->collect();
	}

	public function test_collect_throws_when_branch_output_has_no_current_marker()
	{
		$executor = new FakeSystemCommandExecutor([
			'git branch' => ['  main', '  feature/x'],
			'git log -1 --pretty=format:%H%n%aN%n%ae%n%cN%n%ce%n%s%n%at' => [
				'h', 'an', 'ae', 'cn', 'ce', 'msg', '0',
			],
			'git remote -v' => [],
		]);

		$this->expectException(\RuntimeException::class);

		(new GitInfoCollector($executor))->collect();
	}
}

class FakeSystemCommandExecutor extends SystemCommandExecutor
{
	/** @var array<string, array<int, string>> */
	private $responses;

	public function __construct(array $responses)
	{
		$this->responses = $responses;
	}

	public function execute(string $command): array
	{
		if ( ! array_key_exists($command, $this->responses)) {
			throw new \RuntimeException(sprintf('Unexpected command in test: %s', $command));
		}

		return $this->responses[$command];
	}
}
