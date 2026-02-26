<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Domain\Persistence;

use RuntimeException;
use Snowberry\WpMvc\Contracts\EntityValidatorInterface;
use Snowberry\WpMvc\Contracts\TermDTO;
use Snowberry\WpMvc\Contracts\TermMetaRepositoryInterface;
use Snowberry\WpMvc\Contracts\TermRepositoryInterface;

/**
 * @template T of object
 */
abstract class AbstractTaxonomyRepository
{
	public function __construct(
		protected TermRepositoryInterface $termRepository,
		protected TermMetaRepositoryInterface $termMetaRepository,
		protected ?EntityValidatorInterface $validator = null
	)
	{
	}

	abstract protected function taxonomy(): string;

	/**
	 * @return T
	 */
	abstract protected function map(TermDTO $term): object;

	/**
	 * @param T $entity
	 */
	abstract protected function extractTermId(object $entity): ?int;

	/**
	 * @param T $entity
	 * @return array<string, mixed>
	 */
	abstract protected function extractTermData(object $entity): array;

	protected function acfContext(int $termId): string
	{
		return "term_{$termId}";
	}

	/**
	 * @return T|null
	 */
	public function find(int $id): ?object
	{
		$term = $this->termRepository->find($id);

		if ($term === null || $term->taxonomy !== $this->taxonomy()) {
			return null;
		}

		return $this->map($term);
	}

	/**
	 * @return array<int, T>
	 */
	public function all(): array
	{
		return array_map(
			fn(TermDTO $term): object => $this->map($term),
			$this->termRepository->findByTaxonomy($this->taxonomy())
		);
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

		$termId = $this->extractTermId($entity);
		$data = $this->extractTermData($entity);
		$data['taxonomy'] = $this->taxonomy();

		if ($termId === null) {
			$termId = $this->termRepository->insert($data);
		} else {
			$this->termRepository->update($termId, $data);
		}

		$reloaded = $this->find($termId);
		if ($reloaded === null) {
			throw new RuntimeException('Unable to reload taxonomy entity after save.');
		}

		return $reloaded;
	}

	public function delete(int $id): void
	{
		$this->termRepository->delete($id);
	}
}
