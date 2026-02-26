<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

use RuntimeException;

final class AuthorizationException extends RuntimeException
{
	private string $ability;
	private string $resource;
	private int $userId;

	public function __construct(string $ability, object|string $resource, int $userId = 0)
	{
		$this->ability = $ability;
		$this->resource = is_object($resource) ? $resource::class : $resource;
		$this->userId = $userId;

		parent::__construct(
			sprintf(
				'Authorization denied for ability "%s" on resource "%s" for user ID %d.',
				$this->ability,
				$this->resource,
				$this->userId,
			)
		);
	}

	public function ability(): string
	{
		return $this->ability;
	}

	public function resource(): string
	{
		return $this->resource;
	}

	public function userId(): int
	{
		return $this->userId;
	}
}
