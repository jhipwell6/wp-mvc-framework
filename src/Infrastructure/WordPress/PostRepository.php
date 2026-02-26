<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use InvalidArgumentException;
use Snowberry\WpMvc\Contracts\PostQueryBuilderInterface;
use Snowberry\WpMvc\Contracts\PostRepositoryInterface;
use Snowberry\WpMvc\Contracts\PostDTO;

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

	public function findMany( array $ids ): array
	{
		$normalizedIds = array_values( $ids );

		foreach ( $normalizedIds as $id ) {
			if ( ! is_int( $id ) || $id <= 0 ) {
				throw new InvalidArgumentException( 'findMany expects an array of positive integer post IDs.' );
			}
		}

		if ( $normalizedIds === [] ) {
			return [];
		}

		$posts = get_posts([
			'post__in' => array_values( array_unique( $normalizedIds ) ),
			'posts_per_page' => -1,
			'orderby' => 'post__in',
			'post_status' => 'any',
		]);

		$mappedById = [];

		foreach ( $posts as $post ) {
			$mappedById[ $post->ID ] = $this->map( $post );
		}

		$ordered = [];

		foreach ( $normalizedIds as $id ) {
			if ( isset( $mappedById[ $id ] ) ) {
				$ordered[ $id ] = $mappedById[ $id ];
			}
		}

		return $ordered;
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

	public function query(): PostQueryBuilderInterface
	{
		return new WordPressPostQueryBuilder();
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
