<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Contracts\MetaRepositoryInterface;

final class MetaRepository implements MetaRepositoryInterface
{

	public function get( int $postId, string $key ): mixed
	{
		return get_post_meta( $postId, $key, true );
	}

	public function update( int $postId, string $key, mixed $value ): void
	{
		update_post_meta( $postId, $key, $value );
	}

	public function delete( int $postId, string $key ): void
	{
		delete_post_meta( $postId, $key );
	}

	public function all( int $postId ): array
	{
		return get_post_meta( $postId );
	}
}
