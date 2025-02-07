<?php

namespace Mtvtd\LaravelStats\Helpers;

class SystemCommandExecutor
{
	public function execute(string $command): array
	{
		if ( ! \function_exists('exec')) {
			throw new \RuntimeException(sprintf('exec does not exist, failed to execute command: %s', $command));
		}

		exec($command, $result, $returnValue);

		if ($returnValue === 0) {
			/** @var string[] */
			return $result;
		}

		throw new \RuntimeException(sprintf('Failed to execute command: %s', $command), $returnValue);
	}
}
