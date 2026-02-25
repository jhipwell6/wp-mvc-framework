<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface MetaRepositoryInterface
{

	public function get( int $postId, string $key ): mixed;

	public function update( int $postId, string $key, mixed $value ): void;

	public function delete( int $postId, string $key ): void;

	public function all( int $postId ): array;
}
