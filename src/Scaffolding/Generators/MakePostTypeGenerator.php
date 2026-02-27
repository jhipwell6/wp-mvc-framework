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

final class MakePostTypeGenerator implements ScaffoldGeneratorInterface
{

	public function __construct(
		private ProjectLocatorInterface $project,
		private ProjectManifestInterface $manifest,
		private FileStubRepository $stubs,
		private ScaffoldWriter $writer,
		private FieldProviderInterface $fieldProvider,
		private AcfTypeMapper $typeMapper,
	)
	{
	}

	public function generate( string $name, array $options = [] ): ScaffoldResult
	{
		$force = (bool) ( $options['force'] ?? false );
		$withController = (bool) ( $options['with-controller'] ?? false );

		$slug = strtolower( $name );
		$class = Naming::studly( $slug );

		$label = $options['label'] ?? Naming::title( $slug );
		$supports = $options['supports'] ?? [ 'title', 'editor' ];
		$archive = $options['archive'] ?? true;
		$rewrite = $this->buildRewriteOption( $slug, $options['rewrite'] ?? null );

		$appNamespace = $this->manifest->namespace();

		$pluginRoot = rtrim( $this->project->pluginRoot(), '/' );
		$contentRoot = $pluginRoot . '/' . $this->manifest->path( 'content' );
		$domainRoot = $pluginRoot . '/' . $this->manifest->path( 'domain' );

		$result = new ScaffoldResult();

		$context = $this->buildContext( $slug, $class, $label, $supports, $archive, $rewrite, ! empty( $options['with_acf'] ) );

		$this->writer->writeTemplate(
			$result,
			"{$contentRoot}/PostTypes/{$slug}.php",
			$this->stubs->get( 'post-type/definition.stub.php' ),
			$context,
			$force
		);

		$this->writer->writeTemplate(
			$result,
			"{$domainRoot}/PostTypes/{$class}/Generated/{$class}Base.php",
			$this->stubs->get( 'post-type/entity.stub.php' ),
			$context,
			$force
		);

		$this->writer->writeTemplate(
			$result,
			"{$domainRoot}/PostTypes/{$class}/Generated/{$class}RepositoryBase.php",
			$this->stubs->get( 'post-type/repository.stub.php' ),
			$context,
			$force
		);

		$this->writer->writeTemplate(
			$result,
			"{$domainRoot}/PostTypes/{$class}/{$class}.php",
			$this->stubs->get( 'post-type/entity.concrete.stub.php' ),
			$context,
			false
		);

		$this->writer->writeTemplate(
			$result,
			"{$domainRoot}/PostTypes/{$class}/{$class}Repository.php",
			$this->stubs->get( 'post-type/repository.concrete.stub.php' ),
			$context,
			false
		);

		if ( $withController ) {
			$this->writer->writeTemplate(
				$result,
				"{$pluginRoot}/src/Controllers/{$class}Controller.php",
				$this->stubs->get( 'post-type/controller.stub.php' ),
				$context,
				false
			);
		}

		$result->notes[] = "Next: load src/Content/PostTypes/{$slug}.php into the registration registry (client provider later).";

		return $result;
	}

	public function refreshFromAcf( string $name, bool $force = true ): ScaffoldResult
	{
		$slug = strtolower( $name );
		$class = Naming::studly( $slug );

		$pluginRoot = rtrim( $this->project->pluginRoot(), '/' );
		$domainRoot = $pluginRoot . '/' . $this->manifest->path( 'domain' );

		$context = $this->buildContext(
			$slug,
			$class,
			Naming::title( $slug ),
			[ 'title', 'editor' ],
			true,
			$this->buildRewriteOption( $slug, null ),
			true
		);

		$result = new ScaffoldResult();

		$this->writer->writeTemplate(
			$result,
			"{$domainRoot}/PostTypes/{$class}/Generated/{$class}Base.php",
			$this->stubs->get( 'post-type/entity.stub.php' ),
			$context,
			$force
		);

		$this->writer->writeTemplate(
			$result,
			"{$domainRoot}/PostTypes/{$class}/Generated/{$class}RepositoryBase.php",
			$this->stubs->get( 'post-type/repository.stub.php' ),
			$context,
			$force
		);

		$result->notes[] = "Refreshed ACF-backed generated bases for '{$slug}'.";

		return $result;
	}

	/**
	 * @param array<int, string> $supports
	 * @return array<string, mixed>
	 */
	private function buildContext( string $slug, string $class, string $label, array $supports, mixed $archive, string $rewrite, bool $withAcf ): array
	{
		$appNamespace = $this->manifest->namespace();

		$context = [
			'app_namespace' => $appNamespace,
			'post_type' => [
				'slug' => $slug,
				'class' => $class,
				'label' => $label,
				'supports_php' => $this->exportPhpArray( $supports ),
				'archive_php' => $archive === true ? 'true' : ($archive === false ? 'false' : var_export( $archive, true )),
				'rewrite_php' => $rewrite,
				'fields' => '',
				'field_hydration' => '',
				'field_meta_persistence' => '',
			],
		];

		$relationshipFields = [];

		if ( $withAcf ) {
			$fields = $this->fieldProvider->fieldsForPostType( $slug );

			if ( ! empty( $fields ) ) {
				$propertyLines = [];
				$hydrationLines = [];
				$metaPersistenceLines = [];

				foreach ( $fields as $field ) {
					$type = $this->typeMapper->phpType( $field );
					$metaKey = $field->name;

					if ( $field->type === 'relationship' ) {
						$relationshipFields[] = $field;
						$hydrationLines[] = $this->generateRelationshipHydration( $field );
						$metaPersistenceLines[] = $this->generateRelationshipMetaPersistence( $field );
						continue;
					}

					if ( $field->type === 'repeater' ) {
						$propertyLines[] = "public array \${$field->name} = [],";
						$hydrationLines[] = $this->generateRepeaterHydration( $field );
						$metaPersistenceLines[] = "        \$this->metaRepository->update(\$postId, '{$metaKey}', \$entity->{$metaKey});";
						continue;
					}

					$nullable = $field->required ? '' : '?';
					$default = $field->required ? '' : ' = null';
					$propertyLines[] = "public {$nullable}{$type} \${$field->name}{$default},";

					if ( $type === '\\DateTimeImmutable' ) {
						$hydrationLines[] = "{$metaKey}: (\$value = \$this->metaRepository->get(\$post->ID, '{$metaKey}')) ? new \\DateTimeImmutable(\$value) : null,";
						$metaPersistenceLines[] = "        if (\$entity->{$metaKey} === null) {\n            \$this->metaRepository->delete(\$postId, '{$metaKey}');\n        } else {\n            \$this->metaRepository->update(\$postId, '{$metaKey}', \$entity->{$metaKey}->format(DATE_ATOM));\n        }";
						continue;
					}

					$hydrationLines[] = "{$metaKey}: \$this->metaRepository->get(\$post->ID, '{$metaKey}'),";
					if ( $field->required ) {
						$metaPersistenceLines[] = "        \$this->metaRepository->update(\$postId, '{$metaKey}', \$entity->{$metaKey});";
					} else {
						$metaPersistenceLines[] = "        if (\$entity->{$metaKey} === null) {\n            \$this->metaRepository->delete(\$postId, '{$metaKey}');\n        } else {\n            \$this->metaRepository->update(\$postId, '{$metaKey}', \$entity->{$metaKey});\n        }";
					}
				}

				$context['post_type']['fields'] = implode( "\n", $propertyLines );
				$context['post_type']['field_hydration'] = implode( "\n", $hydrationLines );
				$context['post_type']['field_meta_persistence'] = implode( "\n", $metaPersistenceLines );
			}
		}

		$context['post_type'] = array_merge(
			$context['post_type'],
			$this->buildRelationshipContext( $relationshipFields, $appNamespace )
		);

		return $context;
	}


	private function buildRewriteOption( string $defaultSlug, mixed $rewriteOption ): string
	{
		if ( $rewriteOption === null || $rewriteOption === '' ) {
			return "['slug' => " . var_export( $defaultSlug, true ) . ", 'with_front' => false]";
		}

		$rewrite = strtolower( trim( (string) $rewriteOption ) );

		if ( in_array( $rewrite, [ '0', 'false', 'off' ], true ) ) {
			return 'false';
		}

		if ( in_array( $rewrite, [ '1', 'true', 'on' ], true ) ) {
			return "['slug' => " . var_export( $defaultSlug, true ) . ", 'with_front' => false]";
		}

		return "['slug' => " . var_export( (string) $rewriteOption, true ) . ", 'with_front' => false]";
	}

	/**
	 * @param array<int, string> $values
	 */
	private function exportPhpArray( array $values ): string
	{
		$items = array_map( fn( $v ) => var_export( (string) $v, true ), $values );
		return '[' . implode( ', ', $items ) . ']';
	}

	private function generateRepeaterHydration( FieldDefinition $field ): string
	{
		return "{$field->name}: array_values(
"
			. "                array_map(
"
			. "                    fn(\$row): array => " . $this->generateRepeaterConstructorArgs( $field ) . ",
"
			. "                    is_array(\$rows = \$this->metaRepository->get(\$post->ID, '{$field->name}')) ? \$rows : []
"
			. "                )
"
			. "            ),";
	}

	private function generateRepeaterConstructorArgs( FieldDefinition $field ): string
	{
		$defaultValue = $this->typeMapper->defaultValue( $field );

		return "(is_array(\$row)
"
			. "                        ? \$row
"
			. "                        : (is_object(\$row)
"
			. "                            ? get_object_vars(\$row)
"
			. "                            : (array) {$defaultValue}))";
	}

	private function generateRelationshipHydration( FieldDefinition $field ): string
	{
		$metaKey = $field->name;

		if ( ! is_string( $field->relatedPostType ) || $field->relatedPostType === '' ) {
			if ( $field->multiple ) {
				return "{$metaKey}Ids: array_map(fn(\$id) => (int) \$id, \$this->metaRepository->get(\$post->ID, '{$metaKey}') ?: []),";
			}

			return "{$metaKey}Id: (\$value = \$this->metaRepository->get(\$post->ID, '{$metaKey}')) ? (int) \$value : null,";
		}

		$relationshipMethod = Naming::camel( $field->relatedPostType ) . 'Repository';
		$relationshipEntity = Naming::studly( $field->relatedPostType );

		if ( $field->multiple ) {
			return "{$metaKey}Ids: array_map(fn(\$id) => (int) \$id, \$this->metaRepository->get(\$post->ID, '{$metaKey}') ?: []),\n"
				. "            {$metaKey}Resolver: fn(array \$ids): array => array_values(array_filter(array_map(fn(int \$id): ?{$relationshipEntity} => \$this->{$relationshipMethod}->find(\$id), \$ids))),";
		}

		return "{$metaKey}Id: (\$value = \$this->metaRepository->get(\$post->ID, '{$metaKey}')) ? (int) \$value : null,\n"
			. "            {$metaKey}Resolver: fn(int \$id): ?{$relationshipEntity} => \$this->{$relationshipMethod}->find(\$id),";
	}

	private function generateRelationshipMetaPersistence( FieldDefinition $field ): string
	{
		$metaKey = $field->name;

		if ( $field->multiple ) {
			return "        \$this->metaRepository->update(\$postId, '{$metaKey}', array_map(fn(int \$id): int => \$id, \$entity->{$metaKey}Ids));";
		}

		return "        if (\$entity->{$metaKey}Id === null) {\n            \$this->metaRepository->delete(\$postId, '{$metaKey}');\n        } else {\n            \$this->metaRepository->update(\$postId, '{$metaKey}', \$entity->{$metaKey}Id);\n        }";
	}

	/**
	 * @param FieldDefinition[] $relationshipFields
	 * @return array<string, string>
	 */
	private function buildRelationshipContext( array $relationshipFields, string $appNamespace ): array
	{
		$defaults = [
			'relationship_entity_uses' => '',
			'relationship_repo_uses' => '',
			'relationship_state_properties' => '',
			'relationship_constructor_params' => '',
			'relationship_constructor_assignments' => '',
			'relationship_repository_constructor_params' => '',
			'relationship_repository_properties' => '',
			'relationship_repository_constructor_assignments' => '',
			'relationship_methods' => '',
		];

		if ( $relationshipFields === [] ) {
			return $defaults;
		}

		$entityUses = [];
		$repoUses = [];
		$stateProperties = [];
		$constructorParams = [];
		$constructorAssignments = [];
		$repositoryConstructor = [];
		$relationshipMethods = [];
		$repositoryProperties = [];
		$repositoryAssignments = [];
		$addedRepositories = [];

		foreach ( $relationshipFields as $field ) {
			if ( ! is_string( $field->relatedPostType ) || $field->relatedPostType === '' ) {
				continue;
			}

			$relatedClass = Naming::studly( $field->relatedPostType );
			$relatedRepo = $relatedClass . 'Repository';
			$relatedRepoVariable = Naming::camel( $field->relatedPostType ) . 'Repository';

			$entityUses[] = "use {$appNamespace}\\Domain\\PostTypes\\{$relatedClass}\\{$relatedClass};";
			$repoUses[] = "use {$appNamespace}\\Domain\\PostTypes\\{$relatedClass}\\{$relatedRepo};";

			if ( ! isset( $addedRepositories[ $relatedRepoVariable ] ) ) {
				$repositoryProperties[] = "    private {$relatedRepo} \${$relatedRepoVariable};";
				$repositoryConstructor[] = "        {$relatedRepo} \${$relatedRepoVariable},";
				$repositoryAssignments[] = "        \$this->{$relatedRepoVariable} = \${$relatedRepoVariable};";
				$addedRepositories[ $relatedRepoVariable ] = true;
			}

			if ( $field->multiple ) {
				$stateProperties[] = "    /** @var list<int> */\n    private array \${$field->name}Ids = [];\n\n    /** @var (null|\\Closure(array): list<{$relatedClass}>) */\n    private ?\\Closure \${$field->name}Resolver = null;\n\n    /** @var list<{$relatedClass}>|null */\n    private ?array \${$field->name}Cache = null;";
				$constructorParams[] = "        array \${$field->name}Ids = [],\n        ?\\Closure \${$field->name}Resolver = null,";
				$constructorAssignments[] = "        \$this->{$field->name}Ids = \${$field->name}Ids;\n        \$this->{$field->name}Resolver = \${$field->name}Resolver;";
				$relationshipMethods[] = "    /**\n     * @return list<{$relatedClass}>\n     */\n    public function {$field->name}(): array\n    {\n        if (\$this->{$field->name}Cache !== null) {\n            return \$this->{$field->name}Cache;\n        }\n\n        if (\$this->{$field->name}Ids === [] || \$this->{$field->name}Resolver === null) {\n            return \$this->{$field->name}Cache = [];\n        }\n\n        return \$this->{$field->name}Cache = (\$this->{$field->name}Resolver)(\$this->{$field->name}Ids);\n    }";
				continue;
			}

			$stateProperties[] = "    private ?int \${$field->name}Id = null;\n\n    /** @var (null|\\Closure(int): ?{$relatedClass}) */\n    private ?\\Closure \${$field->name}Resolver = null;\n\n    private bool \${$field->name}Resolved = false;\n\n    private ?{$relatedClass} \${$field->name}Cache = null;";
			$constructorParams[] = "        ?int \${$field->name}Id = null,\n        ?\\Closure \${$field->name}Resolver = null,";
			$constructorAssignments[] = "        \$this->{$field->name}Id = \${$field->name}Id;\n        \$this->{$field->name}Resolver = \${$field->name}Resolver;";
			$relationshipMethods[] = "    public function {$field->name}(): ?{$relatedClass}\n    {\n        if (\$this->{$field->name}Resolved) {\n            return \$this->{$field->name}Cache;\n        }\n\n        \$this->{$field->name}Resolved = true;\n\n        if (\$this->{$field->name}Id === null || \$this->{$field->name}Resolver === null) {\n            return null;\n        }\n\n        return \$this->{$field->name}Cache = (\$this->{$field->name}Resolver)(\$this->{$field->name}Id);\n    }";
		}

		if ( $stateProperties === [] ) {
			return $defaults;
		}

		return [
			'relationship_entity_uses' => implode( "\n", array_values( array_unique( $entityUses ) ) ),
			'relationship_repo_uses' => implode( "\n", array_values( array_unique( $repoUses ) ) ),
			'relationship_state_properties' => implode( "\n\n", $stateProperties ),
			'relationship_constructor_params' => implode( "\n", $constructorParams ),
			'relationship_constructor_assignments' => implode( "\n", $constructorAssignments ),
			'relationship_repository_constructor_params' => implode( "\n", $repositoryConstructor ),
			'relationship_repository_properties' => implode( "\n", $repositoryProperties ),
			'relationship_repository_constructor_assignments' => implode( "\n", $repositoryAssignments ),
			'relationship_methods' => implode( "\n\n", $relationshipMethods ),
		];
	}
}
