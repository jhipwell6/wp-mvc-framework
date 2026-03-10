<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Core;

use Snowberry\WpMvc\Contracts\LoggerInterface;

final class Logger implements LoggerInterface
{
	private LogRegistry $registry;

	public function __construct( LogRegistry $registry )
	{
		$this->registry = $registry;
	}

	public function debug( string $message, array $context = [] ): void
	{
		$this->log( 'debug', $message, $context );
	}

	public function info( string $message, array $context = [] ): void
	{
		$this->log( 'info', $message, $context );
	}

	public function warning( string $message, array $context = [] ): void
	{
		$this->log( 'warning', $message, $context );
	}

	public function error( string $message, array $context = [] ): void
	{
		$this->log( 'error', $message, $context );
	}

	public function log( string $level, string $message, array $context = [] ): void
	{
		$this->registry
			->default()
			->log( $level, $message, $context );
	}
}
