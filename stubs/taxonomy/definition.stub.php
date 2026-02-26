<?php

declare(strict_types=1);

namespace {{app_namespace}}\Content\Taxonomies;

use Snowberry\WpMvc\Builders\TaxonomyBuilder;

return TaxonomyBuilder::make('{{taxonomy.slug}}')
    ->for({{taxonomy.post_types_php}})
    ->label('{{taxonomy.label}}')
    ->hierarchical({{taxonomy.hierarchical_php}})
    ->rest(true)
    ->build();
