<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Contracts\PostRepositoryInterface;
use Snowberry\WpMvc\Contracts\PostDTO;
use WP_Query;

final class PostRepository implements PostRepositoryInterface
{

	public function find( int $id ): ?PostDTO
	{
		$post = get_post( $id );

		if ( ! $post ) {
			return null;
		}

		return $this->map( $post );
	}

	public function insert( array $data ): int
	{
		return wp_insert_post( $data );
	}

	public function update( int $id, array $data ): bool
	{
		$data['ID'] = $id;
		return wp_update_post( $data ) !== 0;
	}

	public function delete( int $id, bool $force = false ): bool
	{
		return wp_delete_post( $id, $force ) !== false;
	}

	public function query( array $args ): array
	{
		$query = new WP_Query( $args );

		if ( ! empty( $query->posts ) ) {
			update_meta_cache( 'post', wp_list_pluck( $query->posts, 'ID' ) );
		}

		return array_map(
			fn( $post ) => $this->map( $post ),
			$query->posts
		);
	}

	private function map( \WP_Post $post ): PostDTO
	{
		return new PostDTO(
			ID: $post->ID,
			post_type: $post->post_type,
			post_title: $post->post_title,
			post_content: $post->post_content,
			post_status: $post->post_status,
			raw: (array) $post
		);
	}
}
