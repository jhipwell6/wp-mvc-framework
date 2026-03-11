<?php

declare(strict_types=1);

use Snowberry\WpMvc\Contracts\LoggerInterface;

if ( ! function_exists( 'debug_log' ) ) {

	function debug_log( string $message, array $context = [] ): void
	{
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		app( LoggerInterface::class )->debug( $message, $context );
	}

}