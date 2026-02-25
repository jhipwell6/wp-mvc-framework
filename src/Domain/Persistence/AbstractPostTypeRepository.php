<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Domain\Persistence;

use RuntimeException;
use Snowberry\WpMvc\Contracts\EntityValidatorInterface;
use Snowberry\WpMvc\Contracts\MetaRepositoryInterface;
use Snowberry\WpMvc\Contracts\PostDTO;
use Snowberry\WpMvc\Contracts\PostRepositoryInterface;

/**
 * @template T of object
 */
abstract class AbstractPostTypeRepository
{
	public function __construct(
		protected PostRepositoryInterface $postRepository,
		protected MetaRepositoryInterface $metaRepository,
		protected ?EntityValidatorInterface $validator = null
	)
	{
	}

	abstract protected function postType(): string;

	/**
	 * @return T
	 */
	abstract protected function map(PostDTO $post): object;

	/**
	 * @param T $entity
	 */
	abstract protected function saveMeta(int $postId, object $entity): void;

	/**
	 * @param T $entity
	 */
	abstract protected function extractPostId(object $entity): ?int;

	/**
	 * @param T $entity
	 * @return array<string, mixed>
	 */
	abstract protected function extractPostData(object $entity): array;

	/**
	 * @return T|null
	 */
	public function find(int $id): ?object
	{
		$post = $this->postRepository->find($id);

		if ($post === null || $post->post_type !== $this->postType()) {
			return null;
		}

		return $this->map($post);
	}

	/**
	 * @param T $entity
	 * @return T
	 */
	public function save(object $entity): object
	{
		if ($this->validator !== null) {
			$this->validator->validate($entity)->throwIfInvalid();
		}

		$postId = $this->extractPostId($entity);
		$data = $this->extractPostData($entity);
		$data['post_type'] = $this->postType();

		if ($postId === null) {
			$postId = $this->postRepository->insert($data);
		} else {
			$this->postRepository->update($postId, $data);
		}

		$this->saveMeta($postId, $entity);

		$reloaded = $this->find($postId);
		if ($reloaded === null) {
			throw new RuntimeException('Unable to reload entity after save.');
		}

		return $reloaded;
	}

	public function delete(int $id): void
	{
		$this->postRepository->delete($id);
	}
}
