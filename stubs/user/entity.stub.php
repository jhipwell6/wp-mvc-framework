<?php

declare(strict_types=1);

namespace {{ app_namespace }}\Domain\Users\{{ user.class }}\Generated;

class {{ user.class }}Base
{
	/**
	 * @param array<int, string> $roles
	 * @param array<string, bool> $caps
	 */
	public function __construct(
		public ?int $id = null,
		public string $email = '',
		public string $login = '',
		public string $displayName = '',
		public array $roles = [],
		public array $caps = [],
	) {
	}
}
