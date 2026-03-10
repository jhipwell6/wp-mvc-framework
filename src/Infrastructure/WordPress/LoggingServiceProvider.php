<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Contracts\LoggerInterface;
use Snowberry\WpMvc\Core\Container;
use Snowberry\WpMvc\Core\Logger;
use Snowberry\WpMvc\Core\LogRegistry;
use Snowberry\WpMvc\Core\ServiceProvider;

final class LoggingServiceProvider extends ServiceProvider
{
	public function register( Container $container ): void
	{
		$container->singleton(
			LogRegistry::class,
			function () {
				$registry = new LogRegistry();
				$registry->register( 'default', new WordPressLoggerAdapter() );

				return $registry;
			}
		);

		$container->singleton(
			LoggerInterface::class,
			fn( Container $c ) => new Logger( $c->get( LogRegistry::class ) )
		);
	}
}
