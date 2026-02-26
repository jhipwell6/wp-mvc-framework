<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use RuntimeException;
use Snowberry\WpMvc\Contracts\OptionRepositoryInterface;
use WP_Error;

final class WordPressOptionRepository implements OptionRepositoryInterface
{
	public function get(string $key, mixed $default = null): mixed
	{
		$value = get_option($key, $default);

		if ($value instanceof WP_Error) {
			throw new RuntimeException(sprintf('WordPress failed to get option "%s". %s', $key, $value->get_error_message()));
		}

		return $value;
	}

	public function update(string $key, mixed $value, bool $autoload = true): void
	{
		$result = update_option($key, $value, $autoload);

		if ($result instanceof WP_Error) {
			throw new RuntimeException(sprintf('WordPress failed to update option "%s". %s', $key, $result->get_error_message()));
		}

		if ($result === false) {
			throw new RuntimeException(sprintf('WordPress failed to update option "%s".', $key));
		}
	}

	public function delete(string $key): void
	{
		$result = delete_option($key);

		if ($result instanceof WP_Error) {
			throw new RuntimeException(sprintf('WordPress failed to delete option "%s". %s', $key, $result->get_error_message()));
		}

		if ($result === false) {
			throw new RuntimeException(sprintf('WordPress failed to delete option "%s".', $key));
		}
	}

	public function has(string $key): bool
	{
		$value = get_option($key, '__missing__');

		if ($value instanceof WP_Error) {
			throw new RuntimeException(sprintf('WordPress failed to check option "%s". %s', $key, $value->get_error_message()));
		}

		return $value !== '__missing__';
	}
}
