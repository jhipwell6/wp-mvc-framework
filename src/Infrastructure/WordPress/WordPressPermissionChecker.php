<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Contracts\AuthorizationException;
use Snowberry\WpMvc\Contracts\PermissionCheckerInterface;

final class WordPressPermissionChecker implements PermissionCheckerInterface
{
	public function can(string $capability, int $userId = 0): bool
	{
		if ($userId > 0) {
			return user_can($userId, $capability);
		}

		return current_user_can($capability);
	}

	public function cannot(string $capability, int $userId = 0): bool
	{
		return ! $this->can($capability, $userId);
	}

	public function require(string $capability, int $userId = 0): void
	{
		if ($this->cannot($capability, $userId)) {
			throw new AuthorizationException($capability, 'capability', $userId);
		}
	}
}
