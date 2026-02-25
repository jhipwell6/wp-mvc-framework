<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface ProjectLocatorInterface
{

	/**
	 * Return absolute path to the active plugin root where we scaffold into.
	 * Example: /var/www/html/wp-content/plugins/client-app
	 */
	public function pluginRoot(): string;
}
