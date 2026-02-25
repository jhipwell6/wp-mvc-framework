<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Scaffolding\Generators;

use Snowberry\WpMvc\Contracts\ProjectLocatorInterface;
use Snowberry\WpMvc\Contracts\ProjectManifestInterface;
use Snowberry\WpMvc\Contracts\ScaffoldGeneratorInterface;
use Snowberry\WpMvc\Contracts\ScaffoldResult;
use Snowberry\WpMvc\Scaffolding\Naming;
use Snowberry\WpMvc\Scaffolding\ScaffoldWriter;
use Snowberry\WpMvc\Scaffolding\FileStubRepository;
use Snowberry\WpMvc\Contracts\FieldProviderInterface;
use Snowberry\WpMvc\Scaffolding\AcfTypeMapper;

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
		$slug = strtolower( $name );
		$class = Naming::studly( $slug );

		$label = $options['label'] ?? Naming::title( $slug );
		$supports = $options['supports'] ?? [ 'title', 'editor' ];
		$archive = $options['archive'] ?? true;

		$appNamespace = $this->manifest->namespace();

		$pluginRoot = rtrim( $this->project->pluginRoot(), '/' );
		$srcRoot = $pluginRoot . '/' . $this->manifest->path( 'src' );
		$contentRoot = $pluginRoot . '/' . $this->manifest->path( 'content' );
		$domainRoot = $pluginRoot . '/' . $this->manifest->path( 'domain' );

		$result = new ScaffoldResult();

		$context = [
			'app_namespace' => $appNamespace,
			'post_type' => [
				'slug' => $slug,
				'class' => $class,
				'label' => $label,
				'supports_php' => $this->exportPhpArray( $supports ),
				'archive_php' => $archive === true ? 'true' : ($archive === false ? 'false' : var_export( $archive, true )),
			],
		];

		$fields = [];

		if ( ! empty( $options['with_acf'] ) ) {
			$fields = $this->fieldProvider->fieldsForPostType( $slug );
			if ( ! empty( $fields ) ) {
				$propertyLines = [];

				foreach ( $fields as $field ) {
					$phpType = $this->typeMapper->phpType( $field );

					$nullable = $field->required ? '' : '?';
					$default = $field->required ? '' : ' = null';

					$propertyLines[] = "public {$nullable}{$phpType} \${$field->name}{$default};";
				}

				$context['post_type']['fields'] = implode( "\n", $propertyLines );

				$hydrationLines = [];

				foreach ( $fields as $field ) {
					$type = $this->typeMapper->phpType( $field );
					$metaKey = $field->name;

					if ( $field->isRepeater ) {
						$hydrationLines[] = $this->generateRepeaterHydration( $field );
						continue;
					}

					if ( $field->type === 'relationship' ) {
						$hydrationLines[] = $this->generateRelationshipHydration( $field );
						continue;
					}

					if ( $type === '\DateTimeImmutable' ) {
						$hydrationLines[] = "{$metaKey}: (\$value = \$this->meta->get(\$post->ID, '{$metaKey}')) ? new \DateTimeImmutable(\$value) : null,";
					} else {
						$hydrationLines[] = "{$metaKey}: \$this->meta->get(\$post->ID, '{$metaKey}'),";
					}
				}

				$context['post_type']['field_hydration'] = implode( "\n", $hydrationLines );
			}
		}

		// 1) Definition file (framework-friendly, later client provider can load these)
		$this->writer->writeTemplate(
			$result,
			"{$contentRoot}/PostTypes/{$slug}.php",
			$this->stubs->get( 'post-type/definition.php' ),
			$context,
			(bool) ($options['force'] ?? false)
		);

		// 2) Entity + Repository
		$this->writer->writeTemplate(
			$result,
			"{$domainRoot}/PostTypes/{$class}/{$class}.php",
			$this->stubs->get( 'post-type/entity.php' ),
			$context,
			(bool) ($options['force'] ?? false)
		);

		$this->writer->writeTemplate(
			$result,
			"{$domainRoot}/PostTypes/{$class}/{$class}Repository.php",
			$this->stubs->get( 'post-type/repository.php' ),
			$context,
			(bool) ($options['force'] ?? false)
		);

		$result->notes[] = "Next: load src/Content/PostTypes/{$slug}.php into the registration registry (client provider later).";

		return $result;
	}

	/**
	 * @param array<int, string> $values
	 */
	private function exportPhpArray( array $values ): string
	{
		// pretty, predictable output: ['title', 'editor']
		$items = array_map( fn( $v ) => var_export( (string) $v, true ), $values );
		return '[' . implode( ', ', $items ) . ']';
	}

	private function generateRepeaterHydration( FieldDefinition $field ): string
	{
		$className = $this->parentClass . Naming::studly( $field->name );

		return "
        {$field->name}: array_map(
            fn(\$row) => new {$className}(
                " . $this->generateRepeaterConstructorArgs( $field ) . "
            ),
            \$this->meta->get(\$post->ID, '{$field->name}') ?: []
        ),";
	}

	private function generateRelationshipHydration( FieldDefinition $field ): string
	{
		$metaKey = $field->name;

		if ( $field->multiple ) {
			// ACF relationship field (multiple)
			return "{$metaKey}: array_map(
                fn(\$id) => (int) \$id,
                \$this->meta->get(\$post->ID, '{$metaKey}') ?: []
            ),";
		}

		// ACF post_object (single)
		return "{$metaKey}: (\$value = \$this->meta->get(\$post->ID, '{$metaKey}'))
                ? (int) \$value
                : null,";
	}
}
