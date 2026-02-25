<?php

declare(strict_types=1);

namespace {{app_namespace}}\Domain\PostTypes\{{post_type.class}};

{{post_type.relationship_entity_uses}}

final class {{post_type.class}}
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
