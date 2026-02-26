<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Domain\Persistence;

use Snowberry\WpMvc\Contracts\OptionRepositoryInterface;

abstract class AbstractOptionsRepository
{
	public function __construct(protected OptionRepositoryInterface $optionRepository)
	{
	}

	abstract protected function optionPrefix(): string;

	/**
	 * @param array<string, mixed> $raw
	 */
	abstract protected function map(array $raw): object;

	/**
	 * @return array<string, mixed>
	 */
	abstract protected function extractData(object $entity): array;

	public function load(): object
	{
		return $this->map($this->loadRaw());
	}

	public function save(object $entity): void
	{
		$data = $this->extractData($entity);
		$prefix = $this->optionPrefix();
		$keysIndex = $this->keysIndexKey($prefix);

		$previousKeys = $this->optionRepository->get($keysIndex, []);
		if (! is_array($previousKeys)) {
			$previousKeys = [];
		}

		$currentKeys = array_values(array_unique(array_keys($data)));

		foreach ($currentKeys as $key) {
			$this->optionRepository->update($this->prefixedKey($prefix, $key), $data[$key]);
		}

		foreach ($previousKeys as $key) {
			if (! is_string($key) || in_array($key, $currentKeys, true)) {
				continue;
			}

			$this->optionRepository->delete($this->prefixedKey($prefix, $key));
		}

		$this->optionRepository->update($prefix, $data);
		$this->optionRepository->update($keysIndex, $currentKeys);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function loadRaw(): array
	{
		$prefix = $this->optionPrefix();
		$raw = [];

		$grouped = $this->optionRepository->get($prefix, []);
		if (is_array($grouped)) {
			$raw = $grouped;
		}

		$keys = $this->optionRepository->get($this->keysIndexKey($prefix), []);
		if (! is_array($keys)) {
			$keys = [];
		}

		foreach ($keys as $key) {
			if (! is_string($key) || $key === '') {
				continue;
			}

			$raw[$key] = $this->optionRepository->get($this->prefixedKey($prefix, $key));
		}

		return $raw;
	}

	private function keysIndexKey(string $prefix): string
	{
		return $prefix . '__keys';
	}

	private function prefixedKey(string $prefix, string $key): string
	{
		return $prefix . '_' . $key;
	}
}
