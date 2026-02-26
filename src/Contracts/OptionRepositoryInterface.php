<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface OptionRepositoryInterface
{
	public function get(string $key, mixed $default = null): mixed;

	public function update(string $key, mixed $value, bool $autoload = true): void;

	public function delete(string $key): void;

	public function has(string $key): bool;
}
