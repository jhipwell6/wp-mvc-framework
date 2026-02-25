<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface PostRepositoryInterface
{

	public function find( int $id ): ?PostDTO;

	public function insert( array $data ): int;

	public function update( int $id, array $data ): bool;

	public function delete( int $id, bool $force = false ): bool;

	public function query( array $args ): array;
}
