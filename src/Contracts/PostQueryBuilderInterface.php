<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface PostQueryBuilderInterface
{
	public function where(string $field, string $operator, mixed $value): self;

	public function whereMeta(string $key, string $operator, mixed $value): self;

	/**
	 * @param array<int, mixed> $values
	 */
	public function whereIn(string $field, array $values): self;

	public function orderBy(string $field, string $direction = 'ASC'): self;

	public function limit(int $limit): self;

	public function offset(int $offset): self;

	/**
	 * @return array<int, PostDTO>
	 */
	public function get(): array;

	public function first(): ?PostDTO;

	/**
	 * @return array<int, int>
	 */
	public function ids(): array;
}
