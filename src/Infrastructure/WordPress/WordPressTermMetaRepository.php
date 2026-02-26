<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use RuntimeException;
use Snowberry\WpMvc\Contracts\TermMetaRepositoryInterface;

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
}
