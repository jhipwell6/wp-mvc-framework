<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use InvalidArgumentException;
use RuntimeException;
use Snowberry\WpMvc\Contracts\UserMetaRepositoryInterface;
use WP_Error;
use wpdb;

final class WordPressUserMetaRepository implements UserMetaRepositoryInterface
{
	public function get(int $userId, string $key): mixed
	{
		$result = get_user_meta($userId, $key, true);

		if ($result instanceof WP_Error) {
			throw new RuntimeException(
				sprintf('WordPress returned a WP_Error while reading user meta "%s" for user %d: %s', $key, $userId, $result->get_error_message())
			);
		}

		return $result;
	}

	public function update(int $userId, string $key, mixed $value): void
	{
		$result = update_user_meta($userId, $key, $value);

		if ($result instanceof WP_Error) {
			throw new RuntimeException(
				sprintf('WordPress returned a WP_Error while updating user meta "%s" for user %d: %s', $key, $userId, $result->get_error_message())
			);
		}

		if ($result === false) {
			throw new RuntimeException(sprintf('WordPress failed to update user meta "%s" for user %d.', $key, $userId));
		}
	}

	public function delete(int $userId, string $key): void
	{
		$result = delete_user_meta($userId, $key);

		if ($result instanceof WP_Error) {
			throw new RuntimeException(
				sprintf('WordPress returned a WP_Error while deleting user meta "%s" for user %d: %s', $key, $userId, $result->get_error_message())
			);
		}

		if ($result === false) {
			throw new RuntimeException(sprintf('WordPress failed to delete user meta "%s" for user %d.', $key, $userId));
		}
	}

	public function getMany(array $userIds): array
	{
		$normalizedIds = array_values($userIds);

		foreach ($normalizedIds as $userId) {
			if (! is_int($userId) || $userId <= 0) {
				throw new InvalidArgumentException('getMany expects an array of positive integer user IDs.');
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
		$query = "SELECT user_id, meta_key, meta_value FROM {$wpdb->usermeta} WHERE user_id IN ($placeholders)";
		$prepared = $wpdb->prepare($query, $normalizedIds);
		$rows = $wpdb->get_results($prepared);

		if ($wpdb->last_error !== '') {
			throw new RuntimeException(sprintf('Failed to fetch user meta with direct query: %s', $wpdb->last_error));
		}

		$grouped = [];

		foreach ($rows as $row) {
			$userId = (int) $row->user_id;
			$key = (string) $row->meta_key;
			$value = maybe_unserialize($row->meta_value);

			if (array_key_exists($key, $grouped[$userId] ?? [])) {
				$existing = $grouped[$userId][$key];
				$grouped[$userId][$key] = is_array($existing) ? [...$existing, $value] : [$existing, $value];
				continue;
			}

			$grouped[$userId][$key] = $value;
		}

		return $grouped;
	}
}
