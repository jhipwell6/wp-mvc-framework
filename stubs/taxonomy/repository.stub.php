<?php

declare(strict_types=1);

namespace {{app_namespace}}\Domain\Taxonomies\{{taxonomy.class}}\Generated;

use {{app_namespace}}\Domain\Taxonomies\{{taxonomy.class}}\{{taxonomy.class}};
use Snowberry\WpMvc\Contracts\EntityValidatorInterface;
use Snowberry\WpMvc\Contracts\TermDTO;
use Snowberry\WpMvc\Contracts\TermMetaRepositoryInterface;
namespace {{app_namespace}}\Domain\PostTypes\{{taxonomy.class}}\Generated;

use {{app_namespace}}\Domain\PostTypes\{{taxonomy.class}}\{{taxonomy.class}};
use Snowberry\WpMvc\Contracts\TermDTO;
use Snowberry\WpMvc\Contracts\TermRepositoryInterface;
use Snowberry\WpMvc\Domain\Persistence\AbstractTaxonomyRepository;

/**
 * @extends AbstractTaxonomyRepository<{{taxonomy.class}}>
 */
class {{taxonomy.class}}RepositoryBase extends AbstractTaxonomyRepository
{
    public function __construct(
        TermRepositoryInterface $termRepository,
        private TermMetaRepositoryInterface $termMetaRepository,
        ?EntityValidatorInterface $validator = null,
    ) {
        parent::__construct($termRepository, $validator);
    public function __construct(TermRepositoryInterface $termRepository)
    {
        parent::__construct($termRepository);
    }

    protected function taxonomy(): string
    {
        return '{{taxonomy.slug}}';
    }

    protected function map(TermDTO $term): {{taxonomy.class}}
    {
        return new {{taxonomy.class}}(
            id: $term->term_id,
            name: $term->name,
            slug: $term->slug,
            description: $term->description,
            parent: $term->parent,
{{taxonomy.field_hydration}}
        );
    }

    protected function extractTermId(object $entity): ?int
    {
        /** @var {{taxonomy.class}} $entity */
        return $entity->id;
    }

    protected function extractTermData(object $entity): array
    {
        /** @var {{taxonomy.class}} $entity */
        return [
            'name' => $entity->name,
            'slug' => $entity->slug,
            'description' => $entity->description,
            'parent' => $entity->parent,
        ];
    }

    protected function saveMeta(int $termId, object $entity): void
    {
        /** @var {{taxonomy.class}} $entity */
{{taxonomy.field_meta_persistence}}
    }
}
