<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Scaffolding\Generators;

use Snowberry\WpMvc\Contracts\FieldDefinition;
use Snowberry\WpMvc\Contracts\FieldProviderInterface;
use Snowberry\WpMvc\Contracts\ProjectLocatorInterface;
use Snowberry\WpMvc\Contracts\ProjectManifestInterface;
use Snowberry\WpMvc\Contracts\ScaffoldGeneratorInterface;
use Snowberry\WpMvc\Contracts\ScaffoldResult;
use Snowberry\WpMvc\Scaffolding\AcfTypeMapper;
use Snowberry\WpMvc\Scaffolding\FileStubRepository;
use Snowberry\WpMvc\Scaffolding\Naming;
use Snowberry\WpMvc\Scaffolding\ScaffoldWriter;

final class MakeTaxonomyGenerator implements ScaffoldGeneratorInterface
{

    public function __construct(
        private ProjectLocatorInterface $project,
        private ProjectManifestInterface $manifest,
        private FileStubRepository $stubs,
        private ScaffoldWriter $writer,
        private FieldProviderInterface $fieldProvider,
        private AcfTypeMapper $typeMapper,
    ) {
    }

    public function generate(string $name, array $options = []): ScaffoldResult
    {
        $force = (bool) ($options['force'] ?? false);

        $slug = strtolower($name);
        $class = Naming::studly($slug);
        $label = $options['label'] ?? Naming::title($slug);
        $postTypes = $options['post-types'] ?? ['post'];
        $hierarchical = (bool) ($options['hierarchical'] ?? true);
        $rewrite = $this->buildRewriteOption($slug, $options['rewrite'] ?? null);

        $appNamespace = $this->manifest->namespace();

        $pluginRoot = rtrim($this->project->pluginRoot(), '/');
        $contentRoot = $pluginRoot . '/' . $this->manifest->path('content');
        $domainRoot = $pluginRoot . '/' . $this->manifest->path('domain');

        $result = new ScaffoldResult();

        $context = [
            'app_namespace' => $appNamespace,
            'taxonomy' => [
                'slug' => $slug,
                'class' => $class,
                'label' => $label,
                'post_types_php' => $this->exportPhpArray(is_array($postTypes) ? $postTypes : [$postTypes]),
                'hierarchical_php' => $hierarchical ? 'true' : 'false',
                'rewrite_php' => $rewrite,
                'fields' => '',
                'field_hydration' => '',
                'field_meta_persistence' => '',
            ],
        ];

        [$propertyLines, $hydrationLines, $metaPersistenceLines] = $this->buildMetaContext($slug, $options);

        $context['taxonomy']['fields'] = implode("\n", $propertyLines);
        $context['taxonomy']['field_hydration'] = implode("\n", $hydrationLines);
        $context['taxonomy']['field_meta_persistence'] = implode("\n", $metaPersistenceLines);

        $this->writer->writeTemplate(
            $result,
            "{$contentRoot}/Taxonomies/{$slug}.php",
            $this->stubs->get('taxonomy/definition.stub.php'),
            $context,
            $force
        );

        $this->writer->writeTemplate(
            $result,
            "{$domainRoot}/Taxonomies/{$class}/Generated/{$class}Base.php",
            $this->stubs->get('taxonomy/entity.stub.php'),
            $context,
            $force
        );

        $this->writer->writeTemplate(
            $result,
            "{$domainRoot}/Taxonomies/{$class}/Generated/{$class}RepositoryBase.php",
            $this->stubs->get('taxonomy/repository.stub.php'),
            $context,
            $force
        );

        $this->writer->writeTemplate(
            $result,
            "{$domainRoot}/Taxonomies/{$class}/{$class}.php",
            $this->stubs->get('taxonomy/entity.concrete.stub.php'),
            $context,
            false
        );

        $this->writer->writeTemplate(
            $result,
            "{$domainRoot}/Taxonomies/{$class}/{$class}Repository.php",
            $this->stubs->get('taxonomy/repository.concrete.stub.php'),
            $context,
            false
        );

        $result->notes[] = "Generated taxonomy base model/repository for {$slug}.";

        return $result;
    }


    private function buildRewriteOption(string $defaultSlug, mixed $rewriteOption): string
    {
        if ($rewriteOption === null || $rewriteOption === '') {
            return "['slug' => " . var_export($defaultSlug, true) . ", 'with_front' => false]";
        }

        $rewrite = strtolower(trim((string) $rewriteOption));

        if (in_array($rewrite, ['0', 'false', 'off'], true)) {
            return 'false';
        }

        if (in_array($rewrite, ['1', 'true', 'on'], true)) {
            return "['slug' => " . var_export($defaultSlug, true) . ", 'with_front' => false]";
        }

        return "['slug' => " . var_export((string) $rewriteOption, true) . ", 'with_front' => false]";
    }

    /**
     * @return array{0: list<string>, 1: list<string>, 2: list<string>}
     */
    private function buildMetaContext(string $taxonomy, array $options): array
    {
        $propertyLines = [];
        $hydrationLines = [];
        $metaPersistenceLines = [];

        foreach ($this->nativeMetaFields($options) as $metaKey) {
            $propertyLines[] = "        public mixed \${$metaKey} = null,";
            $hydrationLines[] = "            {$metaKey}: \$this->termMetaRepository->get(\$term->term_id, '{$metaKey}'),";
            $metaPersistenceLines[] = "        if (\$entity->{$metaKey} === null) {\n            \$this->termMetaRepository->delete(\$termId, '{$metaKey}');\n        } else {\n            \$this->termMetaRepository->update(\$termId, '{$metaKey}', \$entity->{$metaKey});\n        }";
        }

        foreach ($this->acfFieldsForTaxonomy($taxonomy, $options) as $field) {
            $type = $this->typeMapper->phpType($field);
            $metaKey = $field->name;

            if ($field->type === 'repeater') {
                $propertyLines[] = "        public array \${$metaKey} = [],";
                $hydrationLines[] = "            {$metaKey}: is_array(\$value = \$this->termMetaRepository->get(\$term->term_id, '{$metaKey}')) ? \$value : [],";
                $metaPersistenceLines[] = "        \$this->termMetaRepository->update(\$termId, '{$metaKey}', \$entity->{$metaKey});";
                continue;
            }

            $nullable = $field->required ? '' : '?';
            $default = $field->required ? '' : ' = null';
            $propertyLines[] = "        public {$nullable}{$type} \${$metaKey}{$default},";

            if ($type === '\\DateTimeImmutable') {
                $hydrationLines[] = "            {$metaKey}: (\$value = \$this->termMetaRepository->get(\$term->term_id, '{$metaKey}')) ? new \\DateTimeImmutable((string) \$value) : null,";
                $metaPersistenceLines[] = "        if (\$entity->{$metaKey} === null) {\n            \$this->termMetaRepository->delete(\$termId, '{$metaKey}');\n        } else {\n            \$this->termMetaRepository->update(\$termId, '{$metaKey}', \$entity->{$metaKey}->format(DATE_ATOM));\n        }";
                continue;
            }

            $hydrationLines[] = "            {$metaKey}: \$this->termMetaRepository->get(\$term->term_id, '{$metaKey}'),";

            if ($field->required) {
                $metaPersistenceLines[] = "        \$this->termMetaRepository->update(\$termId, '{$metaKey}', \$entity->{$metaKey});";
                continue;
            }

            $metaPersistenceLines[] = "        if (\$entity->{$metaKey} === null) {\n            \$this->termMetaRepository->delete(\$termId, '{$metaKey}');\n        } else {\n            \$this->termMetaRepository->update(\$termId, '{$metaKey}', \$entity->{$metaKey});\n        }";
        }

        return [$propertyLines, $hydrationLines, $metaPersistenceLines];
    }

    /**
     * @return list<string>
     */
    private function nativeMetaFields(array $options): array
    {
        $raw = $options['meta'] ?? [];

        if (! is_array($raw)) {
            return [];
        }

        $fields = [];

        foreach ($raw as $metaKey) {
            if (! is_string($metaKey) || $metaKey === '') {
                continue;
            }

            $fields[] = $metaKey;
        }

        return array_values(array_unique($fields));
    }

    /**
     * @param array<string, mixed> $options
     * @return FieldDefinition[]
     */
    private function acfFieldsForTaxonomy(string $taxonomy, array $options): array
    {
        if (empty($options['with_acf'])) {
            return [];
        }

        if (! method_exists($this->fieldProvider, 'fieldsForTaxonomy')) {
            return [];
        }

        /** @var callable(string): array $resolver */
        $resolver = [$this->fieldProvider, 'fieldsForTaxonomy'];
        $fields = $resolver($taxonomy);

        if (! is_array($fields)) {
            return [];
        }

        return array_values(
            array_filter(
                $fields,
                static fn(mixed $field): bool => $field instanceof FieldDefinition
            )
        );
    }

    /**
     * @param array<int, string> $values
     */
    private function exportPhpArray(array $values): string
    {
        $items = array_map(static fn(string $value): string => var_export($value, true), $values);
        return '[' . implode(', ', $items) . ']';
    }
}
