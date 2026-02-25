<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure;

use Snowberry\WpMvc\Contracts\FilesystemInterface;
use RuntimeException;

final class LocalFilesystem implements FilesystemInterface
{

	public function exists( string $path ): bool
	{
		return file_exists( $path );
	}

	public function read( string $path ): string
	{
		$contents = @file_get_contents( $path );
		if ( $contents === false ) {
			throw new RuntimeException( "Unable to read file: {$path}" );
		}
		return $contents;
	}

	public function write( string $path, string $contents, bool $overwrite = false ): void
	{
		if ( ! $overwrite && $this->exists( $path ) ) {
			throw new RuntimeException( "File already exists: {$path}" );
		}

		$dir = dirname( $path );
		$this->ensureDir( $dir );

		$ok = @file_put_contents( $path, $contents );
		if ( $ok === false ) {
			throw new RuntimeException( "Unable to write file: {$path}" );
		}
	}

	public function mkdir( string $path ): void
	{
		if ( $this->exists( $path ) ) {
			return;
		}
		$ok = @mkdir( $path, 0775, true );
		if ( ! $ok ) {
			throw new RuntimeException( "Unable to create directory: {$path}" );
		}
	}

	public function ensureDir( string $path ): void
	{
		if ( ! is_dir( $path ) ) {
			$this->mkdir( $path );
		}
	}
}
