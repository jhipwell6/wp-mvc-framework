<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

/** @template T of object */
interface PolicyInterface
{
	public function supports(object $resource): bool;

	public function can(string $ability, object $resource, int $userId = 0): bool;
}
