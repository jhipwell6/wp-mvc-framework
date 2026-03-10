<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Core;

use RuntimeException;
use Snowberry\WpMvc\Contracts\LoggerInterface;

final class LogRegistry
{
	private const DEFAULT_CHANNEL = 'default';

	/**
	 * @var array<string, LoggerInterface>
	 */
	private array $channels = [];

	public function register( string $name, LoggerInterface $logger ): void
	{
		$this->channels[$name] = $logger;
	}

	public function get( string $name ): LoggerInterface
	{
		if ( ! isset( $this->channels[$name] ) ) {
			throw new RuntimeException( "Log channel [{$name}] is not registered." );
		}

		return $this->channels[$name];
	}

	public function default(): LoggerInterface
	{
		return $this->get( self::DEFAULT_CHANNEL );
	}
}
