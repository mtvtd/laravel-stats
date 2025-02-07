<?php

namespace Spaanproductions\ManageLaravelStats;

use ReflectionClass;
use ReflectionFunction;
use Composer\InstalledVersions;
use Illuminate\Console\Scheduling\Event;
use Illuminate\Console\Scheduling\CallbackEvent;

class ManageLaravelStats
{
	public function version()
	{
		return InstalledVersions::getVersion('spaanproductions/manage-laravel-stats');
	}

	public function fingerprintTask(Event $event): string
	{
		if ($event instanceof CallbackEvent) {
			return $this->fingerprintCallbackEvent($event);
		}

		return sprintf('managelaravel:%s', sha1(trim(
			"{$event->expression}.{$event->command}.{$event->description}",
			'.'
		)));
	}

	public function fingerprintCallbackEvent(CallbackEvent $event): string
	{
		$callbackMutex = with(new ReflectionClass($event), function (ReflectionClass $class) use ($event): string {
			$callback = $class->getProperty('callback');
			$callback->setAccessible(true);

			$command = $callback->getValue($event);

			if (is_string($command)) {
				return $command;
			}

			if ( ! is_callable($command)) {
				return md5(serialize($command));
			}

			if (is_object($command) && ($class = get_class($command)) !== 'Closure') {
				return $class;
			}

			tap(new ReflectionFunction($command), function (ReflectionFunction $function) use (&$event) {
				/* @phpstan-ignore-next-line */
				$event->extra = [
					'file' => $function->getClosureScopeClass()->getName(),
					'line' => "{$function->getStartLine()} to {$function->getEndLine()}",
				];
			});

			return '';
		});

		return sprintf('managelaravel:%s', sha1(
			str_replace('..', '.', "{$event->expression}.{$callbackMutex}.{$event->description}")
		));
	}

	public function sanitisedCommand($command): string
	{
		return trim(str_replace([
			"'",
			'"',
			PHP_BINARY,
			'artisan',
		], '', $command ?: ''));
	}
}
