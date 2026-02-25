<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Controller;

use Snowberry\WpMvc\Contracts\ControllerInterface;
use Snowberry\WpMvc\Core\Container;

abstract class AbstractController implements ControllerInterface
{

	public function __construct(
		protected Container $container
	)
	{
		
	}

	protected function action(
		string $hook,
		callable $callback,
		int $priority = 10,
		int $args = 1
	): void
	{
		add_action( $hook, $callback, $priority, $args );
	}

	protected function filter(
		string $hook,
		callable $callback,
		int $priority = 10,
		int $args = 1
	): void
	{
		add_filter( $hook, $callback, $priority, $args );
	}

	protected function rest(
		string $namespace,
		string $route,
		array $args
	): void
	{
		add_action( 'rest_api_init', function () use ( $namespace, $route, $args ) {
			register_rest_route( $namespace, $route, $args );
		} );
	}
}
