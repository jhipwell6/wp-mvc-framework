<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Core;

use Snowberry\WpMvc\Contracts\ControllerInterface;

final class ControllerRegistry
{
	/**
	 * @var ControllerInterface[]
	 */
	private array $controllers = [];

	public function add( ControllerInterface $controller ): void
	{
		$this->controllers[] = $controller;
	}

	public function registerAll(): void
	{
		foreach ( $this->controllers as $controller ) {
			$controller->register();
		}
	}
}
