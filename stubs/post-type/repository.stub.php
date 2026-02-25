<?php

declare(strict_types=1);

namespace {{app_namespace}}\Domain\PostTypes\{{post_type.class}};

use Snowberry\WpMvc\Contracts\PostRepositoryInterface;
use Snowberry\WpMvc\Contracts\MetaRepositoryInterface;
{{post_type.relationship_repo_uses}}

final class {{post_type.class}}Repository
{
    public function __construct(
        private PostRepositoryInterface $posts,
        private MetaRepositoryInterface $meta,
{{post_type.relationship_repository_constructor_params}}
    ) {}

    public function find(int $id): ?{{post_type.class}}
    {
        $post = $this->posts->find($id);

        if (!$post || $post->post_type !== '{{post_type.slug}}') {
            return null;
        }

        return new {{post_type.class}}(
            id: $post->ID,
            title: $post->post_title,
            content: $post->post_content,
			{{post_type.field_hydration}}
        );
    }
}