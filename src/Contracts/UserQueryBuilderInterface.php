<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface UserQueryBuilderInterface
{
	public function where(string $field, string $operator, mixed $value): self;

	public function whereRole(string $role): self;

	public function whereMeta(string $key, string $operator, mixed $value): self;

	public function orderBy(string $field, string $direction = 'ASC'): self;

	public function limit(int $limit): self;

	public function offset(int $offset): self;

	/**
	 * @return array<int, UserDTO>
	 */
	public function get(): array;

	public function first(): ?UserDTO;

	/**
	 * @return array<int, int>
	 */
	public function ids(): array;
}
