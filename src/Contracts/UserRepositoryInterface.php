<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface UserRepositoryInterface
{
	public function find(int $id): ?UserDTO;

	public function findByEmail(string $email): ?UserDTO;

	public function findByLogin(string $login): ?UserDTO;

	/**
	 * @param array<int, int> $ids
	 *
	 * @return array<int, UserDTO>
	 */
	public function findMany(array $ids): array;

	/**
	 * @param array<string, mixed> $data
	 */
	public function insert(array $data): int;

	/**
	 * @param array<string, mixed> $data
	 */
	public function update(int $id, array $data): void;

	public function delete(int $id): void;

	public function query(): UserQueryBuilderInterface;
}
