<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Core;

use Snowberry\WpMvc\Contracts\AuthorizationException;
use Snowberry\WpMvc\Contracts\PolicyInterface;
use Snowberry\WpMvc\Contracts\PolicyRegistryInterface;

final class PolicyRegistry implements PolicyRegistryInterface
{
	/** @var array<string, string> */
	private array $policies = [];

	public function __construct(private Container $container)
	{
	}

	public function add(string $resourceClass, string $policyClass): void
	{
		$this->policies[$resourceClass] = $policyClass;
	}

	public function resolve(object $resource): ?PolicyInterface
	{
		$resourceClass = $resource::class;

		if (isset($this->policies[$resourceClass])) {
			return $this->container->get($this->policies[$resourceClass]);
		}

		foreach ($this->policies as $mappedResourceClass => $policyClass) {
			if ($resource instanceof $mappedResourceClass) {
				return $this->container->get($policyClass);
			}
		}

		return null;
	}

	public function can(string $ability, object $resource, int $userId = 0): bool
	{
		$policy = $this->resolve($resource);

		if ($policy === null) {
			return false;
		}

		if (! $policy->supports($resource)) {
			return false;
		}

		return $policy->can($ability, $resource, $userId);
	}

	public function require(string $ability, object $resource, int $userId = 0): void
	{
		if (! $this->can($ability, $resource, $userId)) {
			throw new AuthorizationException($ability, $resource, $userId);
		}
	}
}
