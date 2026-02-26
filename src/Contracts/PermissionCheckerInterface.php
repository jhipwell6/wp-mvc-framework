<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface PermissionCheckerInterface
{
	public function can(string $capability, int $userId = 0): bool;

	public function cannot(string $capability, int $userId = 0): bool;

	/**
	 * @throws AuthorizationException
	 */
	public function require(string $capability, int $userId = 0): void;
}
