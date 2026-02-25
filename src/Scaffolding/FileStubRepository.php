<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Scaffolding;

use RuntimeException;

final class FileStubRepository
{
	private string $basePath;

	public function __construct()
	{
		// Framework root is 2 levels up from src/
		$this->basePath = dirname( __DIR__, 2 ) . '/stubs';
	}

	public function get( string $relativePath ): string
	{
		$path = $this->basePath . '/' . $relativePath;

		if ( ! file_exists( $path ) ) {
			throw new RuntimeException( "Stub not found: {$relativePath}" );
		}

		return file_get_contents( $path );
	}
}
