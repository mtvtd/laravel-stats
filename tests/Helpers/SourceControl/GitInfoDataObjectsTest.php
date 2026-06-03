<?php

namespace Mtvtd\LaravelStats\Tests\Helpers\SourceControl;

use Mtvtd\LaravelStats\Tests\TestCase;
use Mtvtd\LaravelStats\Helpers\SourceControl\GitInfo;
use Mtvtd\LaravelStats\Helpers\SourceControl\CommitInfo;
use Mtvtd\LaravelStats\Helpers\SourceControl\RemoteInfo;

class GitInfoDataObjectsTest extends TestCase
{
	public function test_commit_info_setters_and_getters_roundtrip()
	{
		$commit = (new CommitInfo())
			->setId('abc123')
			->setAuthorName('Ada')
			->setAuthorEmail('ada@example.com')
			->setCommitterName('Grace')
			->setCommitterEmail('grace@example.com')
			->setMessage('Initial commit')
			->setDate(1717000000);

		$this->assertSame('abc123', $commit->getId());
		$this->assertSame('Ada', $commit->getAuthorName());
		$this->assertSame('ada@example.com', $commit->getAuthorEmail());
		$this->assertSame('Grace', $commit->getCommitterName());
		$this->assertSame('grace@example.com', $commit->getCommitterEmail());
		$this->assertSame('Initial commit', $commit->getMessage());
		$this->assertSame(1717000000, $commit->getDate());
	}

	public function test_commit_info_to_array_has_full_shape()
	{
		$commit = (new CommitInfo())
			->setId('abc123')
			->setAuthorName('Ada')
			->setAuthorEmail('ada@example.com')
			->setCommitterName('Grace')
			->setCommitterEmail('grace@example.com')
			->setMessage('msg')
			->setDate(42);

		$this->assertSame([
			'id' => 'abc123',
			'author_name' => 'Ada',
			'author_email' => 'ada@example.com',
			'committer_name' => 'Grace',
			'committer_email' => 'grace@example.com',
			'message' => 'msg',
			'date' => 42,
		], $commit->toArray());
	}

	public function test_remote_info_setters_and_to_array()
	{
		$remote = (new RemoteInfo())
			->setName('origin')
			->setUrl('git@github.com:mtvtd/laravel-stats.git');

		$this->assertSame('origin', $remote->getName());
		$this->assertSame('git@github.com:mtvtd/laravel-stats.git', $remote->getUrl());
		$this->assertSame([
			'name' => 'origin',
			'url' => 'git@github.com:mtvtd/laravel-stats.git',
		], $remote->toArray());
	}

	public function test_git_info_to_array_includes_branch_head_and_remotes()
	{
		$head = (new CommitInfo())
			->setId('a')->setAuthorName('a')->setAuthorEmail('a')
			->setCommitterName('a')->setCommitterEmail('a')
			->setMessage('a')->setDate(1);

		$origin = (new RemoteInfo())->setName('origin')->setUrl('git@github.com:foo/bar.git');
		$mirror = (new RemoteInfo())->setName('mirror')->setUrl('https://example.com/foo.git');

		$gitInfo = new GitInfo('main', $head, [$origin, $mirror]);

		$this->assertSame('main', $gitInfo->getBranch());
		$this->assertSame($head, $gitInfo->getHead());
		$this->assertSame([$origin, $mirror], $gitInfo->getRemotes());

		$this->assertSame([
			'branch' => 'main',
			'head' => $head->toArray(),
			'remotes' => [$origin->toArray(), $mirror->toArray()],
		], $gitInfo->toArray());
	}
}
