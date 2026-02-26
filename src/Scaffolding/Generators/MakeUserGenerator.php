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

final class MakeUserGenerator implements ScaffoldGeneratorInterface
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

        $pluginRoot = rtrim($this->project->pluginRoot(), '/');
        $domainRoot = $pluginRoot . '/' . $this->manifest->path('domain');
        $appNamespace = $this->manifest->namespace();

        [$propertyLines, $hydrationLines, $metaPersistenceLines] = $this->buildMetaContext($slug, $options);

        $context = [
            'app_namespace' => $appNamespace,
            'user' => [
                'slug' => $slug,
                'class' => $class,
                'fields' => implode("\n", $propertyLines),
                'field_hydration' => implode("\n", $hydrationLines),
                'field_meta_persistence' => implode("\n", $metaPersistenceLines),
            ],
        ];

        $result = new ScaffoldResult();

        $this->writer->writeTemplate(
            $result,
            "{$domainRoot}/Users/{$class}/Generated/{$class}Base.php",
            $this->stubs->get('user.entity.stub.php'),
            $context,
            $force
        );

        $this->writer->writeTemplate(
            $result,
            "{$domainRoot}/Users/{$class}/Generated/{$class}RepositoryBase.php",
            $this->stubs->get('user.repository.stub.php'),
            $context,
            $force
        );
      

		$this->writer->writeTemplate(
			$result,
			"{$domainRoot}/Users/{$class}/{$class}.php",
			$this->stubs->get('user/entity.concrete.stub.php'),
			$context,
			false
		);

		$this->writer->writeTemplate(
			$result,
			"{$domainRoot}/Users/{$class}/{$class}Repository.php",
			$this->stubs->get('user/repository.concrete.stub.php'),
			$context,
			false
		);

        $result->notes[] = "Generated user base model/repository for {$slug}.";

        return $result;
    }

    /**
     * @return array{0: list<string>, 1: list<string>, 2: list<string>}
     */
    private function buildMetaContext(string $userType, array $options): array
    {
        $propertyLines = [];
        $hydrationLines = [];
        $metaPersistenceLines = [];

        foreach ($this->nativeMetaFields($options) as $metaKey) {
            $propertyLines[] = "        public mixed \${$metaKey} = null,";
            $hydrationLines[] = "            {$metaKey}: \$this->userMetaRepository->get(\$user->ID, '{$metaKey}'),";
            $metaPersistenceLines[] = "        if (\$entity->{$metaKey} === null) {\n            \$this->userMetaRepository->delete(\$userId, '{$metaKey}');\n        } else {\n            \$this->userMetaRepository->update(\$userId, '{$metaKey}', \$entity->{$metaKey});\n        }";
        }

        foreach ($this->acfFieldsForUser($userType, $options) as $field) {
            $type = $this->typeMapper->phpType($field);
            $metaKey = $field->name;

            if ($field->type === 'repeater') {
                $propertyLines[] = "        public array \${$metaKey} = [],";
                $hydrationLines[] = "            {$metaKey}: is_array(\$value = \$this->userMetaRepository->get(\$user->ID, '{$metaKey}')) ? \$value : [],";
                $metaPersistenceLines[] = "        \$this->userMetaRepository->update(\$userId, '{$metaKey}', \$entity->{$metaKey});";
                continue;
            }

            $nullable = $field->required ? '' : '?';
            $default = $field->required ? '' : ' = null';
            $propertyLines[] = "        public {$nullable}{$type} \${$metaKey}{$default},";

            if ($type === '\\DateTimeImmutable') {
                $hydrationLines[] = "            {$metaKey}: (\$value = \$this->userMetaRepository->get(\$user->ID, '{$metaKey}')) ? new \\DateTimeImmutable((string) \$value) : null,";
                $metaPersistenceLines[] = "        if (\$entity->{$metaKey} === null) {\n            \$this->userMetaRepository->delete(\$userId, '{$metaKey}');\n        } else {\n            \$this->userMetaRepository->update(\$userId, '{$metaKey}', \$entity->{$metaKey}->format(DATE_ATOM));\n        }";
                continue;
            }

            $hydrationLines[] = "            {$metaKey}: \$this->userMetaRepository->get(\$user->ID, '{$metaKey}'),";

            if ($field->required) {
                $metaPersistenceLines[] = "        \$this->userMetaRepository->update(\$userId, '{$metaKey}', \$entity->{$metaKey});";
                continue;
            }

            $metaPersistenceLines[] = "        if (\$entity->{$metaKey} === null) {\n            \$this->userMetaRepository->delete(\$userId, '{$metaKey}');\n        } else {\n            \$this->userMetaRepository->update(\$userId, '{$metaKey}', \$entity->{$metaKey});\n        }";
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
     * @return FieldDefinition[]
     */
    private function acfFieldsForUser(string $userType, array $options): array
    {
        if (empty($options['with_acf'])) {
            return [];
        }

        if (! method_exists($this->fieldProvider, 'fieldsForUser')) {
            return [];
        }

        /** @var callable(string): array $resolver */
        $resolver = [$this->fieldProvider, 'fieldsForUser'];
        $fields = $resolver($userType);

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
}
