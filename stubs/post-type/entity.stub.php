<?php

declare(strict_types=1);

namespace {{app_namespace}}\Domain\PostTypes\{{post_type.class}};

final class {{post_type.class}}
{
    public function __construct(
        public ?int $id = null,
        public string $title = '',
        public string $content = '',
		{{post_type.fields}}
    ) {}
}