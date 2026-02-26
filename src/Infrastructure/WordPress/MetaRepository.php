<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use InvalidArgumentException;
use Snowberry\WpMvc\Contracts\MetaRepositoryInterface;
use wpdb;

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

	public function getMany( array $postIds ): array
	{
		$normalizedIds = array_values( $postIds );

		foreach ( $normalizedIds as $postId ) {
			if ( ! is_int( $postId ) || $postId <= 0 ) {
				throw new InvalidArgumentException( 'getMany expects an array of positive integer post IDs.' );
			}
		}

		if ( $normalizedIds === [] ) {
			return [];
		}

		global $wpdb;

		if ( ! $wpdb instanceof wpdb ) {
			throw new InvalidArgumentException( 'Global $wpdb is not available.' );
		}

		$placeholders = implode( ', ', array_fill( 0, count( $normalizedIds ), '%d' ) );
		$query = "SELECT post_id, meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id IN ($placeholders)";
		$prepared = $wpdb->prepare( $query, $normalizedIds );
		$rows = $wpdb->get_results( $prepared );

		$grouped = [];

		foreach ( $rows as $row ) {
			$postId = (int) $row->post_id;
			$key = (string) $row->meta_key;
			$value = maybe_unserialize( $row->meta_value );

			if ( array_key_exists( $key, $grouped[ $postId ] ?? [] ) ) {
				$existing = $grouped[ $postId ][ $key ];
				$grouped[ $postId ][ $key ] = is_array( $existing ) ? [ ...$existing, $value ] : [ $existing, $value ];
				continue;
			}

			$grouped[ $postId ][ $key ] = $value;
		}

		return $grouped;
	}
}
