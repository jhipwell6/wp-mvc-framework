<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface TermMetaRepositoryInterface
{
	public function get(int $termId, string $key): mixed;

	public function update(int $termId, string $key, mixed $value): void;

	public function delete(int $termId, string $key): void;

	/**
	 * @param array<int, int> $termIds
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function getMany(array $termIds): array;
}
