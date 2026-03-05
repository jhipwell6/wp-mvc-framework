<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Support;

final class AssetContext
{
	public const FRONTEND = 'frontend';
	public const ADMIN = 'admin';
	public const EDITOR = 'editor';
	public const BLOCK = 'block';
	public const LOGIN = 'login';

	/**
	 * @return string[]
	 */
	public static function all(): array
	{
		return [
			self::FRONTEND,
			self::ADMIN,
			self::EDITOR,
			self::BLOCK,
			self::LOGIN,
		];
	}
}
