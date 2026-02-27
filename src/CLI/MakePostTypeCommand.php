<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\CLI;

use Snowberry\WpMvc\Contracts\ScaffoldGeneratorInterface;
use Snowberry\WpMvc\Scaffolding\Generators\MakePostTypeGenerator;

final class MakePostTypeCommand extends AbstractCommand
{

	public function __construct(
		private ScaffoldGeneratorInterface $generator
	)
	{
		
	}

	public function name(): string
	{
		return 'wp-mvc make:post-type';
	}

	public function description(): string
	{
		return 'Generate scaffolding for a new post type (definition, entity, repository).';
	}

	public function handle( array $args, array $assocArgs ): void
	{
		$slug = $args[0] ?? '';
		if ( $slug === '' ) {
			$this->error( 'Usage: wp wp-mvc make:post-type <slug> [--label=] [--supports=title,editor] [--archive=1|0] [--rewrite=<slug|false>] [--force]' );
			return;
		}

		$supports = isset( $assocArgs['supports'] ) ? array_filter( array_map( 'trim', explode( ',', (string) $assocArgs['supports'] ) ) ) : null;

		$options = [
			'label' => $assocArgs['label'] ?? null,
			'supports' => $supports ?: null,
			'archive' => isset( $assocArgs['archive'] ) ? (bool) intval( (string) $assocArgs['archive'] ) : null,
			'rewrite' => $assocArgs['rewrite'] ?? null,
			'force' => isset( $assocArgs['force'] ),
			'with_acf' => isset( $assocArgs['with-acf'] ),
			// leave app_namespace overridable for later
			'app_namespace' => $assocArgs['namespace'] ?? 'ClientApp',
		];

		// Strip nulls so generator defaults apply
		$options = array_filter( $options, fn( $v ) => $v !== null );

		$result = $this->generator->generate( $slug, $options );

		foreach ( $result->created as $path ) {
			$this->log( "Created: {$path}" );
		}
		foreach ( $result->skipped as $path ) {
			$this->log( "Skipped: {$path}" );
		}
		foreach ( $result->notes as $note ) {
			$this->log( $note );
		}

		$this->success( "Scaffold complete for '{$slug}'." );
	}
}
