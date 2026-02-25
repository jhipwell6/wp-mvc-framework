<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface CommandInterface
{

	public function name(): string;

	public function description(): string;

	public function handle( array $args, array $assocArgs ): void;
}
