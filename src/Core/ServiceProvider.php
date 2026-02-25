<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Core;

abstract class ServiceProvider
{

	abstract public function register( Container $container ): void;

	public function boot( Container $container ): void
	{
		// optional override
	}
}
