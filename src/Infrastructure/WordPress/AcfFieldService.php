<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use RuntimeException;
use Snowberry\WpMvc\Contracts\AcfFieldServiceInterface;

final class AcfFieldService implements AcfFieldServiceInterface
{

	/**
	 * @param int|string|null $context Taxonomy terms must use the explicit "term_{$termId}" context string.
	 */
	public function get( string $field, int|string|null $context = null ): mixed
	{
		$this->ensureFunctionExists( 'get_field' );

		return get_field( $field, $context );
	}

	public function update( string $field, mixed $value, int|string|null $context = null ): void
	{
		$this->ensureFunctionExists( 'update_field' );

		update_field( $field, $value, $context );
	}

	public function delete( string $field, int|string|null $context = null ): void
	{
		$this->ensureFunctionExists( 'delete_field' );

		delete_field( $field, $context );
	}

	private function ensureFunctionExists( string $functionName ): void
	{
		if ( ! function_exists( $functionName ) ) {
			throw new RuntimeException( sprintf( 'ACF function "%s" is not available. Ensure ACF is active.', $functionName ) );
		}
	}
}
