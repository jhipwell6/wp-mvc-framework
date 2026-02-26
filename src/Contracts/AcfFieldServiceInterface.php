<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface AcfFieldServiceInterface
{

	/**
	 * @param int|string|null $context Post ID, user context, global options context ('option'),
	 *                                 or a taxonomy term context like "term_{$termId}".
	 */
	public function get( string $field, int|string|null $context = null ): mixed;

	/**
	 * @param int|string|null $context Post ID, user context, global options context ('option'),
	 *                                 or a taxonomy term context like "term_{$termId}".
	 */
	public function update( string $field, mixed $value, int|string|null $context = null ): void;

	/**
	 * @param int|string|null $context Post ID, user context, global options context ('option'),
	 *                                 or a taxonomy term context like "term_{$termId}".
	 */
	public function delete( string $field, int|string|null $context = null ): void;
}
