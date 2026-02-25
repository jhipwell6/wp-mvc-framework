<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Core;

use InvalidArgumentException;
use RuntimeException;
use Snowberry\WpMvc\Contracts\BlockDefinitionInterface;

final class BlockRegistry
{
	/** @var array<string, BlockDefinitionInterface> */
	private array $blocks = [];

	public function add( BlockDefinitionInterface $block ): void
	{
		$name = $block->name();

		if ( isset( $this->blocks[$name] ) ) {
			throw new InvalidArgumentException( "Duplicate block definition: {$name}" );
		}

		$this->blocks[$name] = $block;
	}

	public function registerAll(): void
	{
		if ( ! function_exists( 'acf_register_block_type' ) ) {
			throw new RuntimeException( 'ACF block registration requires ACF PRO to be active.' );
		}

		add_action( 'acf/init', function (): void {
			foreach ( $this->blocks as $definition ) {
				$settings = $definition->settings();
				$settings['name'] = $definition->name();
				$settings['render_callback'] = function ( array $block, string $content = '', bool $isPreview = false, int $postId = 0 ) use ( $definition ): void {
					$definition->render( $block, $content, $isPreview, $postId );
				};

				acf_register_block_type( $settings );
			}
		} );
	}
}
