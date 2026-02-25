<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface SchedulerInterface
{

	public function dispatch( string $hook, array $args = [], int $timestamp = null ): void;
}
