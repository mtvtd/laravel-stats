<?php

namespace Mtvtd\LaravelStats\Tests\Helpers;

use Mtvtd\LaravelStats\Tests\TestCase;
use Mtvtd\LaravelStats\Helpers\SystemCommandExecutor;

class SystemCommandExecutorTest extends TestCase
{
	public function setUp(): void
	{
		parent::setUp();

		if ( ! function_exists('exec')) {
			$this->markTestSkipped('exec() is not available in this environment.');
		}
	}

	public function test_execute_returns_command_output_as_array_of_lines()
	{
		$result = (new SystemCommandExecutor())->execute('echo hello');

		$this->assertSame(['hello'], $result);
	}

	public function test_execute_throws_when_command_exits_non_zero()
	{
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessageMatches('/Failed to execute command/');

		(new SystemCommandExecutor())->execute('exit 1');
	}
}
