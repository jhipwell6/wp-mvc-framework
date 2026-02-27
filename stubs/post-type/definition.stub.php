<?php

declare(strict_types=1);

namespace {{app_namespace}}\Content\PostTypes;

use Snowberry\WpMvc\Builders\PostTypeBuilder;

return PostTypeBuilder::make('{{post_type.slug}}')
    ->label('{{post_type.label}}')
    ->supports({{post_type.supports_php}})
    ->archive({{post_type.archive_php}})
    ->rewrite({{post_type.rewrite_php}})
    ->rest(true)
    ->build();
