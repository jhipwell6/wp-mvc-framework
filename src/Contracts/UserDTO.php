<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

final class UserDTO
{
	/**
	 * @param array<int, string> $roles
	 * @param array<string, bool> $caps
	 */
	public function __construct(
		public int $ID,
		public string $user_login,
		public string $user_email,
		public string $display_name,
		public array $roles,
		public array $caps
	)
	{
	}
}
