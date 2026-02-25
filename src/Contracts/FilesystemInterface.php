<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface FilesystemInterface
{

	public function exists( string $path ): bool;

	public function read( string $path ): string;

	public function write( string $path, string $contents, bool $overwrite = false ): void;

	public function mkdir( string $path ): void;

	public function ensureDir( string $path ): void;
}
