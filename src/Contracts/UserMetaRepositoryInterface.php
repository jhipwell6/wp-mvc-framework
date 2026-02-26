<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface UserMetaRepositoryInterface
{
	public function get(int $userId, string $key): mixed;

	public function update(int $userId, string $key, mixed $value): void;

	public function delete(int $userId, string $key): void;

	/**
	 * @param array<int, int> $userIds
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getMany(array $userIds): array;
}
