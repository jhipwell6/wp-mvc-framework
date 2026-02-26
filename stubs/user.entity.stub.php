<?php

declare(strict_types=1);

namespace {{app_namespace}}\Domain\Users\{{user.class}}\Generated;

class {{user.class}}Base
{
    public function __construct(
        public ?int $id = null,
        public string $email = '',
        public string $login = '',
        public string $displayName = '',
        /** @var list<string> */
        public array $roles = [],
        /** @var array<string, bool> */
        public array $caps = [],
{{user.fields}}
    ) {
    }
}
