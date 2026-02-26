<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Support;

use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * @template T
 * @implements IteratorAggregate<int, T>
 */
class Collection implements IteratorAggregate
{
	/**
	 * @var array<int, T>
	 */
	private array $items;

	/**
	 * @param array<int, T> $items
	 */
	public function __construct(array $items = [])
	{
		$this->items = array_values($items);
	}

	/**
	 * @return array<int, T>
	 */
	public function all(): array
	{
		return $this->items;
	}

	/**
	 * @return T|null
	 */
	public function first(): mixed
	{
		return $this->items[0] ?? null;
	}

	/**
	 * @template U
	 * @param callable(T): U $callback
	 * @return Collection<U>
	 */
	public function map(callable $callback): Collection
	{
		/** @var array<int, U> $mapped */
		$mapped = array_map($callback, $this->items);

		return new Collection($mapped);
	}

	/**
	 * @param callable(T): bool $callback
	 * @return Collection<T>
	 */
	public function filter(callable $callback): Collection
	{
		/** @var array<int, T> $filtered */
		$filtered = array_values(array_filter($this->items, $callback));

		return new Collection($filtered);
	}

	/**
	 * @param callable(T): void $callback
	 */
	public function each(callable $callback): void
	{
		foreach ($this->items as $item) {
			$callback($item);
		}
	}

	public function count(): int
	{
		return count($this->items);
	}

	public function isEmpty(): bool
	{
		return $this->count() === 0;
	}

	/**
	 * @return array<int, T>
	 */
	public function toArray(): array
	{
		return $this->all();
	}

	/**
	 * @return array<int, mixed>
	 */
	public function pluck(string $property): array
	{
		$values = [];

		foreach ($this->items as $item) {
			if (is_array($item)) {
				if (!array_key_exists($property, $item)) {
					throw new InvalidArgumentException(sprintf('Unable to pluck "%s" from collection item.', $property));
				}

				$values[] = $item[$property];
				continue;
			}

			if (!is_object($item)) {
				throw new InvalidArgumentException(sprintf('Unable to pluck "%s" from non-object collection item.', $property));
			}

			$values[] = $this->extractObjectPropertyValue($item, $property);
		}

		return $values;
	}

	/**
	 * @return Traversable<int, T>
	 */
	public function getIterator(): Traversable
	{
		return new ArrayIterator($this->items);
	}

	private function extractObjectPropertyValue(object $item, string $property): mixed
	{
		$normalizedProperty = str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $property)));
		$methods = [
			'get' . $normalizedProperty,
			'is' . $normalizedProperty,
			'has' . $normalizedProperty,
		];

		foreach ($methods as $method) {
			if (is_callable([$item, $method])) {
				return $item->{$method}();
			}
		}

		throw new InvalidArgumentException(sprintf('Unable to pluck "%s" from %s. Expected getter method.', $property, $item::class));
	}
}
