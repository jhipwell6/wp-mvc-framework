<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Core\Container;
use Snowberry\WpMvc\Core\ServiceProvider;
use Snowberry\WpMvc\Contracts\AssetManagerInterface;
use Snowberry\WpMvc\Core\AssetManager;
use Snowberry\WpMvc\Core\AssetRegistry;

class AssetServiceProvider extends ServiceProvider
{

	public function register( Container $container ): void
	{
		$container->singleton(
			AssetRegistry::class,
			fn() => new AssetRegistry()
		);

		$container->singleton(
			AssetManagerInterface::class,
			fn( $c ) => new AssetManager(
				$c->get( AssetRegistry::class )
			)
		);

		$container->singleton(
			WordPressAssetLoader::class,
			fn( $c ) => new WordPressAssetLoader(
				$c->get( AssetManagerInterface::class )
			)
		);
	}

	public function boot( Container $container ): void
	{
		$container
			->get( WordPressAssetLoader::class )
			->registerHooks();
	}
}
