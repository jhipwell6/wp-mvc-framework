<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use InvalidArgumentException;
use RuntimeException;
use Snowberry\WpMvc\Contracts\TermMetaRepositoryInterface;
use wpdb;

final class WordPressTermMetaRepository implements TermMetaRepositoryInterface
{
	public function get(int $termId, string $key): mixed
	{
		return get_term_meta($termId, $key, true);
	}

	public function update(int $termId, string $key, mixed $value): void
	{
		$result = update_term_meta($termId, $key, $value);

		if ($result === false) {
			throw new RuntimeException(sprintf('WordPress failed to update term meta "%s" for term %d.', $key, $termId));
		}
	}

	public function delete(int $termId, string $key): void
	{
		$result = delete_term_meta($termId, $key);

		if ($result === false) {
			throw new RuntimeException(sprintf('WordPress failed to delete term meta "%s" for term %d.', $key, $termId));
		}
	}

	public function getMany(array $termIds): array
	{
		$normalizedIds = array_values($termIds);

		foreach ($normalizedIds as $termId) {
			if (! is_int($termId) || $termId <= 0) {
				throw new InvalidArgumentException('getMany expects an array of positive integer term IDs.');
			}
		}

		if ($normalizedIds === []) {
			return [];
		}

		global $wpdb;

		if (! $wpdb instanceof wpdb) {
			throw new RuntimeException('Global $wpdb is not available.');
		}

		$placeholders = implode(', ', array_fill(0, count($normalizedIds), '%d'));
		$query = "SELECT term_id, meta_key, meta_value FROM {$wpdb->termmeta} WHERE term_id IN ($placeholders)";
		$prepared = $wpdb->prepare($query, $normalizedIds);
		$rows = $wpdb->get_results($prepared);

		$grouped = [];

		foreach ($rows as $row) {
			$termId = (int) $row->term_id;
			$key = (string) $row->meta_key;
			$value = maybe_unserialize($row->meta_value);

			if (array_key_exists($key, $grouped[$termId] ?? [])) {
				$existing = $grouped[$termId][$key];
				$grouped[$termId][$key] = is_array($existing) ? [...$existing, $value] : [$existing, $value];
				continue;
			}

			$grouped[$termId][$key] = $value;
		}

		return $grouped;
	}
}
