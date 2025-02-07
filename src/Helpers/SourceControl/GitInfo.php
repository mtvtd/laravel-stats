<?php

namespace Mtvtd\LaravelStats\Helpers\SourceControl;

class GitInfo
{
	/**
	 * Branch name.
	 *
	 * @var string
	 */
	protected $branch;

	/**
	 * Head.
	 *
	 * @var CommitInfo
	 */
	protected $head;

	/**
	 * Remote.
	 *
	 * @var RemoteInfo[]
	 */
	protected $remotes;

	/**
	 * Constructor.
	 *
	 * @param string $branch        branch name
	 * @param CommitInfo $head      hEAD commit
	 * @param RemoteInfo[] $remotes remote repositories
	 */
	public function __construct(string $branch, CommitInfo $head, array $remotes)
	{
		$this->branch = $branch;
		$this->head = $head;
		$this->remotes = $remotes;
	}

	public function toArray(): array
	{
		$remotes = [];

		foreach ($this->remotes as $remote) {
			$remotes[] = $remote->toArray();
		}

		return [
			'branch' => $this->branch,
			'head' => $this->head->toArray(),
			'remotes' => $remotes,
		];
	}

	// accessor

	/**
	 * Return branch name.
	 *
	 */
	public function getBranch(): string
	{
		return $this->branch;
	}

	/**
	 * Return HEAD commit.
	 *
	 */
	public function getHead(): CommitInfo
	{
		return $this->head;
	}

	/**
	 * Return remote repositories.
	 *
	 * @return RemoteInfo[]
	 */
	public function getRemotes(): array
	{
		return $this->remotes;
	}
}
