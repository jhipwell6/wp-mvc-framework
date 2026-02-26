<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Domain\Persistence;

use RuntimeException;
use SplObjectStorage;
use Snowberry\WpMvc\Contracts\EntityValidatorInterface;
use Snowberry\WpMvc\Contracts\MetaRepositoryInterface;
use Snowberry\WpMvc\Contracts\PostDTO;
use Snowberry\WpMvc\Contracts\PostRepositoryInterface;
use Snowberry\WpMvc\Contracts\TermDTO;
use Snowberry\WpMvc\Contracts\TermRepositoryInterface;

/**
 * @template T of object
 */
abstract class AbstractPostTypeRepository
{
	/**
	 * @var SplObjectStorage<T, array<string, mixed>>
	 */
	private SplObjectStorage $metaCache;

	public function __construct(
		protected PostRepositoryInterface $postRepository,
		protected MetaRepositoryInterface $metaRepository,
		protected TermRepositoryInterface $termRepository,
		protected ?EntityValidatorInterface $validator = null
	)
	{
		$this->metaCache = new SplObjectStorage();
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

	/**
	 * @param T[] $entities
	 *
	 * @return array<int, array{entity: T, meta: array<string, mixed>}>
	 */
	public function withMeta(array $entities): array
	{
		$postIds = [];
		$entitiesByPostId = [];

		foreach ($entities as $entity) {
			$postId = $this->extractPostId($entity);
			if ($postId === null) {
				continue;
			}

			$postIds[$postId] = $postId;
			$entitiesByPostId[$postId][] = $entity;
		}

		if ($postIds === []) {
			return [];
		}

		$metaByPostId = $this->metaRepository->getMany(array_values($postIds));
		$results = [];

		foreach ($entitiesByPostId as $postId => $groupedEntities) {
			$meta = $metaByPostId[$postId] ?? [];

			foreach ($groupedEntities as $entity) {
				$this->metaCache[$entity] = $meta;
				$results[] = [
					'entity' => $entity,
					'meta' => $meta,
				];
			}
		}

		return $results;
	}

	/**
	 * @param T[] $entities
	 *
	 * @return array<int, array{entity: T, terms: array<int, TermDTO>}>
	 */
	public function withTerms(array $entities, string $taxonomy): array
	{
		$postIds = [];
		$entitiesByPostId = [];

		foreach ($entities as $entity) {
			$postId = $this->extractPostId($entity);
			if ($postId === null) {
				continue;
			}

			$postIds[$postId] = $postId;
			$entitiesByPostId[$postId][] = $entity;
		}

		if ($postIds === []) {
			return [];
		}

		$termsByPostId = $this->termRepository->findForPosts(array_values($postIds), $taxonomy);
		$results = [];

		foreach ($entitiesByPostId as $postId => $groupedEntities) {
			$terms = $termsByPostId[$postId] ?? [];

			foreach ($groupedEntities as $entity) {
				$results[] = [
					'entity' => $entity,
					'terms' => $terms,
				];
			}
		}

		return $results;
	}

	/**
	 * @param T[] $entities
	 * @param string $relationProperty
	 *
	 * @return array<int, array{entity: T, relation: PostDTO|array<int, PostDTO>|null}>
	 */
	public function withRelation(array $entities, string $relationProperty): array
	{
		$entityRelationIds = [];
		$relationIds = [];

		foreach ($entities as $index => $entity) {
			if (!isset($entity->{$relationProperty})) {
				$entityRelationIds[$index] = [];
				continue;
			}

			$relationValue = $entity->{$relationProperty};
			$ids = [];

			if (is_int($relationValue) || is_string($relationValue)) {
				$ids[] = (int) $relationValue;
			} elseif (is_array($relationValue)) {
				foreach ($relationValue as $value) {
					if (is_int($value) || is_string($value)) {
						$ids[] = (int) $value;
					}
				}
			}

			$ids = array_values(array_filter($ids, static fn (int $id): bool => $id > 0));
			$entityRelationIds[$index] = $ids;

			foreach ($ids as $id) {
				$relationIds[$id] = $id;
			}
		}

		$relatedById = [];
		if ($relationIds !== []) {
			$relatedById = $this->postRepository->findMany(array_values($relationIds));
		}

		$results = [];
		foreach ($entities as $index => $entity) {
			$ids = $entityRelationIds[$index] ?? [];

			if ($ids === []) {
				$results[] = [
					'entity' => $entity,
					'relation' => null,
				];
				continue;
			}

			if (count($ids) === 1) {
				$results[] = [
					'entity' => $entity,
					'relation' => $relatedById[$ids[0]] ?? null,
				];
				continue;
			}

			$related = [];
			foreach ($ids as $id) {
				if (isset($relatedById[$id])) {
					$related[$id] = $relatedById[$id];
				}
			}

			$results[] = [
				'entity' => $entity,
				'relation' => $related,
			];
		}

		return $results;
	}

	/**
	 * @return array<int, TermDTO>
	 */
	protected function terms(int $postId, string $taxonomy): array
	{
		return $this->termRepository->findForPost($postId, $taxonomy);
	}
}
