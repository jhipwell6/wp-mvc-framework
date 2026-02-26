<?php

declare(strict_types=1);

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
}
