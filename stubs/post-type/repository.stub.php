<?php

declare(strict_types=1);

namespace {{app_namespace}}\Domain\PostTypes\{{post_type.class}}\Generated;

use {{app_namespace}}\Domain\PostTypes\{{post_type.class}}\{{post_type.class}};
use Snowberry\WpMvc\Contracts\EntityValidatorInterface;
use Snowberry\WpMvc\Contracts\MetaRepositoryInterface;
use Snowberry\WpMvc\Contracts\PostDTO;
use Snowberry\WpMvc\Contracts\PostRepositoryInterface;
use Snowberry\WpMvc\Domain\Persistence\AbstractPostTypeRepository;
{{post_type.relationship_repo_uses}}

/**
 * @extends AbstractPostTypeRepository<{{post_type.class}}>
 */
class {{post_type.class}}RepositoryBase extends AbstractPostTypeRepository
{
{{post_type.relationship_repository_properties}}

    public function __construct(
        PostRepositoryInterface $postRepository,
        MetaRepositoryInterface $metaRepository,
        ?EntityValidatorInterface $validator = null,
{{post_type.relationship_repository_constructor_params}}
    ) {
{{post_type.relationship_repository_constructor_assignments}}
        parent::__construct($postRepository, $metaRepository, $validator);
    }

    protected function postType(): string
    {
        return '{{post_type.slug}}';
    }

    protected function map(PostDTO $post): {{post_type.class}}
    {
        return new {{post_type.class}}(
            id: $post->ID,
            title: $post->post_title,
            content: $post->post_content,
            {{post_type.field_hydration}}
        );
    }

    protected function extractPostId(object $entity): ?int
    {
        /** @var {{post_type.class}} $entity */
        return $entity->id;
    }

    protected function extractPostData(object $entity): array
    {
        /** @var {{post_type.class}} $entity */
        return [
            'post_title' => $entity->title,
            'post_content' => $entity->content,
        ];
    }

    protected function saveMeta(int $postId, object $entity): void
    {
        /** @var {{post_type.class}} $entity */
{{post_type.field_meta_persistence}}
    }
}
