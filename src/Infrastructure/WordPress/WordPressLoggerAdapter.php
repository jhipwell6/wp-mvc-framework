<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Contracts\LoggerInterface;
use Snowberry\WpMvc\Support\LogEntry;

final class WordPressLoggerAdapter implements LoggerInterface
{
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
		if ( ! defined( 'WP_DEBUG' ) || WP_DEBUG !== true ) {
			return;
		}

		$entry = new LogEntry( strtoupper( $level ), $message, $context );
		$contextJson = wp_json_encode( $entry->context() );

		if ( $contextJson === false ) {
			$contextJson = '{}';
		}

		error_log( sprintf(
			'[WP-MVC][%s] %s %s',
			$entry->level(),
			$entry->message(),
			$contextJson
		) );
	}
}
