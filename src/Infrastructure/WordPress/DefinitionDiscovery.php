<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Contracts\DefinitionDiscoveryInterface;
use Snowberry\WpMvc\Contracts\ProjectLocatorInterface;
use Snowberry\WpMvc\Contracts\ProjectManifestInterface;
use Snowberry\WpMvc\Contracts\RegistrationRegistryInterface;
use Snowberry\WpMvc\Contracts\PostTypeDefinition;
use Snowberry\WpMvc\Contracts\TaxonomyDefinition;
use RuntimeException;

final class DefinitionDiscovery implements DefinitionDiscoveryInterface
{

	public function __construct(
		private ProjectLocatorInterface $locator,
		private ProjectManifestInterface $manifest,
		private RegistrationRegistryInterface $registry,
	)
	{
		
	}

	public function discover(): void
	{
		$pluginRoot = rtrim( $this->locator->pluginRoot(), '/' );
		$contentRoot = $pluginRoot . '/' . $this->manifest->path( 'content' );

		$this->discoverPostTypes( $contentRoot . '/PostTypes' );
		$this->discoverTaxonomies( $contentRoot . '/Taxonomies' );
		$this->discoverAcfGroups( $contentRoot . '/Acf' );
	}

	private function discoverPostTypes( string $dir ): void
	{
		foreach ( $this->phpFiles( $dir ) as $file ) {
			$value = $this->requireFile( $file );

			if ( ! $value instanceof PostTypeDefinition ) {
				throw new RuntimeException( "Post type definition must return PostTypeDefinition: {$file}" );
			}

			$this->registry->addPostType( $value );
		}
	}

	private function discoverTaxonomies( string $dir ): void
	{
		foreach ( $this->phpFiles( $dir ) as $file ) {
			$value = $this->requireFile( $file );

			if ( ! $value instanceof TaxonomyDefinition ) {
				throw new RuntimeException( "Taxonomy definition must return TaxonomyDefinition: {$file}" );
			}

			$this->registry->addTaxonomy( $value );
		}
	}

	private function discoverAcfGroups( string $dir ): void
	{
		foreach ( $this->phpFiles( $dir ) as $file ) {
			$value = $this->requireFile( $file );

			if ( ! is_array( $value ) ) {
				throw new RuntimeException( "ACF group definition must return array: {$file}" );
			}

			$this->registry->addFieldGroup( $value );
		}
	}

	/**
	 * @return string[]
	 */
	private function phpFiles( string $dir ): array
	{
		if ( ! is_dir( $dir ) ) {
			return [];
		}

		$files = glob( rtrim( $dir, '/' ) . '/*.php' ) ?: [];
		sort( $files ); // stable order

		return $files;
	}

	private function requireFile( string $file ): mixed
	{
		/** @noinspection PhpIncludeInspection */
		return require $file;
	}
}
