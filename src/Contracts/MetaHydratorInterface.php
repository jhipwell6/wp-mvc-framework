<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface MetaHydratorInterface
{

	public function hydrate( object $model, int $postId ): void;
}
