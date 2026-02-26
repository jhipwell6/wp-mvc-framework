<?php

declare(strict_types=1);

namespace {{app_namespace}}\Domain\PostTypes\{{taxonomy.class}}\Generated;

class {{taxonomy.class}}Base
{
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public string $slug = '',
        public string $description = '',
        public int $parent = 0,
    ) {
    }
}
