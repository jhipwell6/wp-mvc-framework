<?php

declare(strict_types=1);

namespace {{app_namespace}}\Domain\PostTypes\{{post_type.class}}\Generated;

{{post_type.relationship_entity_uses}}

class {{post_type.class}}Base
{
    {{post_type.relationship_state_properties}}

    public function __construct(
        public ?int $id = null,
        public string $title = '',
        public string $content = '',
		{{post_type.fields}}
        {{post_type.relationship_constructor_params}}
    ) {
        {{post_type.relationship_constructor_assignments}}
    }

    {{post_type.relationship_methods}}
}
