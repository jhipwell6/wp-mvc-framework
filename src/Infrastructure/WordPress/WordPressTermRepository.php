<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use RuntimeException;
use Snowberry\WpMvc\Contracts\TermDTO;
use Snowberry\WpMvc\Contracts\TermRepositoryInterface;
use WP_Error;
use WP_Term;

final class WordPressTermRepository implements TermRepositoryInterface
{
	public function find(int $termId): ?TermDTO
	{
		$term = get_term($termId);

		if ($term === null || $term === false) {
			return null;
		}

		if ($term instanceof WP_Error) {
			$this->throwWordPressError($term, 'Unable to fetch term.');
		}

		if (! $term instanceof WP_Term) {
			throw new RuntimeException('Unexpected term type returned by get_term().');
		}

		return $this->map($term);
	}

	public function findByTaxonomy(string $taxonomy): array
	{
		$terms = get_terms([
			'taxonomy' => $taxonomy,
			'hide_empty' => false,
		]);

		if ($terms instanceof WP_Error) {
			$this->throwWordPressError($terms, 'Unable to fetch taxonomy terms.');
		}

		return array_map(fn(WP_Term $term): TermDTO => $this->map($term), $terms);
	}

	public function findForPost(int $postId, string $taxonomy): array
	{
		$terms = wp_get_object_terms($postId, $taxonomy);

		if ($terms instanceof WP_Error) {
			$this->throwWordPressError($terms, 'Unable to fetch post terms.');
		}

		return array_map(fn(WP_Term $term): TermDTO => $this->map($term), $terms);
	}

	/**
	 * @param array<int, int> $termIds
	 */
	public function assignToPost(int $postId, string $taxonomy, array $termIds): void
	{
		$result = wp_set_object_terms($postId, $termIds, $taxonomy);

		if ($result instanceof WP_Error) {
			$this->throwWordPressError($result, 'Unable to assign terms to post.');
		}
	}

	public function insert(array $data): int
	{
		$name = $data['name'] ?? null;
		$taxonomy = $data['taxonomy'] ?? null;

		if (! is_string($name) || $name === '') {
			throw new RuntimeException('Term insert requires a non-empty "name" value.');
		}

		if (! is_string($taxonomy) || $taxonomy === '') {
			throw new RuntimeException('Term insert requires a non-empty "taxonomy" value.');
		}

		$args = $data;
		unset($args['name'], $args['taxonomy']);

		$result = wp_insert_term($name, $taxonomy, $args);

		if ($result instanceof WP_Error) {
			$this->throwWordPressError($result, 'Unable to insert term.');
		}

		if (! isset($result['term_id'])) {
			throw new RuntimeException('WordPress did not return a term_id after insertion.');
		}

		return (int) $result['term_id'];
	}

	public function update(int $termId, array $data): void
	{
		$taxonomy = $data['taxonomy'] ?? $this->resolveTaxonomy($termId);

		if (! is_string($taxonomy) || $taxonomy === '') {
			throw new RuntimeException('Unable to resolve taxonomy for term update.');
		}

		unset($data['taxonomy']);

		$result = wp_update_term($termId, $taxonomy, $data);

		if ($result instanceof WP_Error) {
			$this->throwWordPressError($result, 'Unable to update term.');
		}
	}

	public function delete(int $termId): void
	{
		$taxonomy = $this->resolveTaxonomy($termId);
		$result = wp_delete_term($termId, $taxonomy);

		if ($result instanceof WP_Error) {
			$this->throwWordPressError($result, 'Unable to delete term.');
		}

		if ($result === false) {
			throw new RuntimeException(sprintf('WordPress failed to delete term %d.', $termId));
		}
	}

	private function resolveTaxonomy(int $termId): string
	{
		$term = $this->find($termId);

		if ($term === null) {
			throw new RuntimeException(sprintf('Unable to resolve taxonomy for missing term %d.', $termId));
		}

		return $term->taxonomy;
	}

	private function map(WP_Term $term): TermDTO
	{
		return new TermDTO(
			term_id: (int) $term->term_id,
			name: (string) $term->name,
			slug: (string) $term->slug,
			taxonomy: (string) $term->taxonomy,
			description: (string) $term->description,
			parent: (int) $term->parent
		);
	}

	private function throwWordPressError(WP_Error $error, string $message): never
	{
		throw new RuntimeException(sprintf('%s %s', $message, $error->get_error_message()));
	}
}
