<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use InvalidArgumentException;
use RuntimeException;
use Snowberry\WpMvc\Contracts\UserDTO;
use Snowberry\WpMvc\Contracts\UserQueryBuilderInterface;
use Snowberry\WpMvc\Contracts\UserRepositoryInterface;
use WP_Error;
use WP_User;

final class WordPressUserRepository implements UserRepositoryInterface
{
	public function find(int $id): ?UserDTO
	{
		$user = get_user_by('ID', $id);

		if ($user === false) {
			return null;
		}

		return $this->map($user);
	}

	public function findByEmail(string $email): ?UserDTO
	{
		$user = get_user_by('email', $email);

		if ($user === false) {
			return null;
		}

		return $this->map($user);
	}

	public function findByLogin(string $login): ?UserDTO
	{
		$user = get_user_by('login', $login);

		if ($user === false) {
			return null;
		}

		return $this->map($user);
	}

	public function findMany(array $ids): array
	{
		$normalizedIds = array_values($ids);

		foreach ($normalizedIds as $id) {
			if (! is_int($id) || $id <= 0) {
				throw new InvalidArgumentException('findMany expects an array of positive integer user IDs.');
			}
		}

		if ($normalizedIds === []) {
			return [];
		}

		$users = get_users([
			'include' => array_values(array_unique($normalizedIds)),
			'orderby' => 'include',
		]);

		$mappedById = [];

		foreach ($users as $user) {
			if (! $user instanceof WP_User) {
				continue;
			}

			$mappedById[$user->ID] = $this->map($user);
		}

		$ordered = [];

		foreach ($normalizedIds as $id) {
			if (isset($mappedById[$id])) {
				$ordered[$id] = $mappedById[$id];
			}
		}

		return $ordered;
	}

	public function insert(array $data): int
	{
		$result = wp_insert_user($data);

		if ($result instanceof WP_Error) {
			$this->throwWordPressError($result, 'Unable to insert user.');
		}

		return (int) $result;
	}

	public function update(int $id, array $data): void
	{
		$data['ID'] = $id;
		$result = wp_update_user($data);

		if ($result instanceof WP_Error) {
			$this->throwWordPressError($result, 'Unable to update user.');
		}
	}

	public function delete(int $id): void
	{
		$result = wp_delete_user($id);

		if ($result === false) {
			throw new RuntimeException(sprintf('WordPress failed to delete user %d.', $id));
		}
	}

	public function query(): UserQueryBuilderInterface
	{
		return new WordPressUserQueryBuilder();
	}

	private function map(WP_User $user): UserDTO
	{
		$roles = array_values(array_map('strval', $user->roles));

		$caps = [];
		foreach ($user->caps as $capability => $allowed) {
			$caps[(string) $capability] = (bool) $allowed;
		}

		return new UserDTO(
			ID: (int) $user->ID,
			user_login: (string) $user->user_login,
			user_email: (string) $user->user_email,
			display_name: (string) $user->display_name,
			roles: $roles,
			caps: $caps,
		);
	}

	private function throwWordPressError(WP_Error $error, string $message): never
	{
		throw new RuntimeException(sprintf('%s %s', $message, $error->get_error_message()));
	}
}
