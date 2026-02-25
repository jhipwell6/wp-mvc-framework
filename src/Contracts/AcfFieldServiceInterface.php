<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface AcfFieldServiceInterface
{

	public function get( string $field, int|string|null $context = null ): mixed;

	public function update( string $field, mixed $value, int|string|null $context = null ): void;

	public function delete( string $field, int|string|null $context = null ): void;
}
