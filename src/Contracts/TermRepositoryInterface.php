<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface TermRepositoryInterface
{
	public function find(int $termId): ?TermDTO;

	/**
	 * @return array<int, TermDTO>
	 */
	public function findByTaxonomy(string $taxonomy): array;

	/**
	 * @return array<int, TermDTO>
	 */
	public function findForPost(int $postId, string $taxonomy): array;

	/**
	 * @param array<int, int> $termIds
	 */
	public function assignToPost(int $postId, string $taxonomy, array $termIds): void;

	/**
	 * @param array<string, mixed> $data
	 */
	public function insert(array $data): int;

	/**
	 * @param array<string, mixed> $data
	 */
	public function update(int $termId, array $data): void;

	public function delete(int $termId): void;
}
