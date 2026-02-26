<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface PolicyRegistryInterface
{
	public function add(string $resourceClass, string $policyClass): void;

	public function resolve(object $resource): ?PolicyInterface;

	public function can(string $ability, object $resource, int $userId = 0): bool;

	/**
	 * @throws AuthorizationException
	 */
	public function require(string $ability, object $resource, int $userId = 0): void;
}
