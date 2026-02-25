<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\CLI;

use Snowberry\WpMvc\Contracts\CommandInterface;

final class CommandRegistry
{
	private array $commands = [];

	public function add( CommandInterface $command ): void
	{
		$this->commands[] = $command;
	}

	public function registerAll(): void
	{
		if ( ! defined( 'WP_CLI' ) || ! \WP_CLI ) {
			return;
		}

		foreach ( $this->commands as $command ) {
			\WP_CLI::add_command(
				$command->name(),
				function ( $args, $assocArgs ) use ( $command ) {
					$command->handle( $args, $assocArgs );
				},
				[
					'shortdesc' => $command->description(),
				]
			);
		}
	}
}
