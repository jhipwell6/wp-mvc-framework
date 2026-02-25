<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use RuntimeException;
use Snowberry\WpMvc\Contracts\BlockDefinitionInterface;
use Snowberry\WpMvc\Contracts\ProjectManifestInterface;
use Snowberry\WpMvc\Core\BlockRegistry;
use Snowberry\WpMvc\Core\Container;
use Snowberry\WpMvc\Core\ServiceProvider;

final class BlockServiceProvider extends ServiceProvider
{
	public function register( Container $container ): void
	{
		$container->singleton( BlockRegistry::class, fn() => new BlockRegistry() );
	}

	public function boot( Container $container ): void
	{
		$manifest = $container->get( ProjectManifestInterface::class );
		$registry = $container->get( BlockRegistry::class );

		foreach ( $manifest->blocks() as $blockClass ) {
			if ( ! class_exists( $blockClass ) ) {
				throw new RuntimeException( "Manifest block class [{$blockClass}] does not exist." );
			}

			$blockDefinition = $container->get( $blockClass );

			if ( ! $blockDefinition instanceof BlockDefinitionInterface ) {
				throw new RuntimeException( "Manifest block class [{$blockClass}] must implement BlockDefinitionInterface." );
			}

			$registry->add( $blockDefinition );
		}

		$registry->registerAll();
	}
}
