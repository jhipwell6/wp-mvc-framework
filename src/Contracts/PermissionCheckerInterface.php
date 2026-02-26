<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface PermissionCheckerInterface
{
	public function hasCapability(string $capability, int $userId = 0): bool;

	public function requireCapability(string $capability, int $userId = 0): void;

	public function hasRole(string $role, int $userId = 0): bool;

	public function requireRole(string $role, int $userId = 0): void;

	/**
	 * @param array<int, string> $roles
	 */
	public function hasAnyRole(array $roles, int $userId = 0): bool;

	/**
	 * @param array<int, string> $roles
	 */
	public function hasAllRoles(array $roles, int $userId = 0): bool;
}
