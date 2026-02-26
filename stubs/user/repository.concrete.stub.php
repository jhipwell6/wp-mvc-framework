<?php

declare(strict_types=1);

namespace {{ app_namespace }}\Domain\Users\{{ user.class }};

use Snowberry\WpMvc\Contracts\UserDTO;
use Snowberry\WpMvc\Domain\Persistence\AbstractUserRepository;

/**
 * @extends AbstractUserRepository<{{ user.class }}>
 */
final class {{ user.class }}Repository extends AbstractUserRepository
{
	protected function map(UserDTO $user): object
	{
		return new {{ user.class }}(
			id: $user->ID,
			email: $user->user_email,
			login: $user->user_login,
			displayName: $user->display_name,
			roles: $user->roles,
			caps: $user->caps,
		);
	}

	protected function extractUserId(object $entity): ?int
	{
		if (! $entity instanceof {{ user.class }}) {
			return null;
		}

		return $entity->id;
	}

	protected function extractUserData(object $entity): array
	{
		if (! $entity instanceof {{ user.class }}) {
			return [];
		}

		$data = [
			'user_email' => $entity->email,
			'user_login' => $entity->login,
			'display_name' => $entity->displayName,
		];

		if ($entity->roles !== []) {
			$data['role'] = $entity->roles[0];
		}

		if ($entity->caps !== []) {
			$data['caps'] = $entity->caps;
		}

		return $data;
	}
}
