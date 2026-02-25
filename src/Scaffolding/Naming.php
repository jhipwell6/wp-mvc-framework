<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Scaffolding;

final class Naming
{

	public static function studly( string $value ): string
	{
		$value = preg_replace( '/[^a-zA-Z0-9]+/', ' ', $value ) ?? $value;
		$value = ucwords( strtolower( trim( $value ) ) );
		return str_replace( ' ', '', $value );
	}

	public static function title( string $slug ): string
	{
		$slug = str_replace( [ '-', '_' ], ' ', $slug );
		return ucwords( $slug );
	}
}
