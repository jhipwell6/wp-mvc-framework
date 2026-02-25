<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\CLI;

use Snowberry\WpMvc\Contracts\CommandInterface;

abstract class AbstractCommand implements CommandInterface
{

	abstract public function name(): string;

	abstract public function description(): string;

	abstract public function handle( array $args, array $assocArgs ): void;

	protected function success( string $message ): void
	{
		\WP_CLI::success( $message );
	}

	protected function error( string $message ): void
	{
		\WP_CLI::error( $message );
	}

	protected function log( string $message ): void
	{
		\WP_CLI::log( $message );
	}
}
