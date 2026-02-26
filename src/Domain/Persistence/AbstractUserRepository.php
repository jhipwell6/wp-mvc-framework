<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Domain\Persistence;

use RuntimeException;
use Snowberry\WpMvc\Contracts\UserDTO;
use Snowberry\WpMvc\Contracts\UserRepositoryInterface;

/**
 * @template T of object
 */
abstract class AbstractUserRepository
{
	public function __construct(
		protected UserRepositoryInterface $userRepository
	)
	{
	}

	/**
	 * @return T
	 */
	abstract protected function map(UserDTO $user): object;

	/**
	 * @param T $entity
	 */
	abstract protected function extractUserId(object $entity): ?int;

	/**
	 * @param T $entity
	 * @return array<string, mixed>
	 */
	abstract protected function extractUserData(object $entity): array;

	/**
	 * @return T|null
	 */
	public function find(int $id): ?object
	{
		$user = $this->userRepository->find($id);

		if ($user === null) {
			return null;
		}

		return $this->map($user);
	}

	/**
	 * @param T $entity
	 * @return T
	 */
	public function save(object $entity): object
	{
		$userId = $this->extractUserId($entity);
		$data = $this->extractUserData($entity);

		if ($userId === null) {
			$userId = $this->userRepository->insert($data);
		} else {
			$this->userRepository->update($userId, $data);
		}

		$reloaded = $this->find($userId);
		if ($reloaded === null) {
			throw new RuntimeException('Unable to reload user entity after save.');
		}

		return $reloaded;
	}

	public function delete(int $id): void
	{
		$this->userRepository->delete($id);
	}
}
