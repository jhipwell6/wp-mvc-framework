<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Core\Container;
use Snowberry\WpMvc\Core\ServiceProvider;
use Snowberry\WpMvc\Core\RegistrationRegistry;
use Snowberry\WpMvc\Contracts\PostRepositoryInterface;
use Snowberry\WpMvc\Contracts\MetaRepositoryInterface;
use Snowberry\WpMvc\Contracts\HttpClientInterface;
use Snowberry\WpMvc\Contracts\CacheInterface;
use Snowberry\WpMvc\Contracts\PostTypeRegistrarInterface;
use Snowberry\WpMvc\Contracts\TaxonomyRegistrarInterface;
use Snowberry\WpMvc\Contracts\FieldGroupRegistrarInterface;
use Snowberry\WpMvc\Contracts\RegistrationRegistryInterface;

final class WordPressServiceProvider extends ServiceProvider
{

	public function register( Container $container ): void
	{
		/*
		  |--------------------------------------------------------------------------
		  | Core Infrastructure Adapters
		  |--------------------------------------------------------------------------
		 */
		
		$container->singleton(
			PostRepositoryInterface::class,
			fn() => new PostRepository()
		);

		$container->singleton(
			MetaRepositoryInterface::class,
			fn() => new MetaRepository()
		);

		$container->singleton(
			HttpClientInterface::class,
			fn() => new WpHttpClient()
		);

		$container->singleton(
			CacheInterface::class,
			fn() => new TransientCache()
		);

		/*
		  |--------------------------------------------------------------------------
		  | Registration Adapters
		  |--------------------------------------------------------------------------
		 */

		$container->singleton(
			PostTypeRegistrarInterface::class,
			fn() => new PostTypeRegistrar()
		);

		$container->singleton(
			TaxonomyRegistrarInterface::class,
			fn() => new TaxonomyRegistrar()
		);

		$container->singleton(
			FieldGroupRegistrarInterface::class,
			fn() => new AcfFieldGroupRegistrar()
		);

		/*
		  |--------------------------------------------------------------------------
		  | Registration Registry
		  |--------------------------------------------------------------------------
		 */

		$container->singleton(
			RegistrationRegistryInterface::class,
			function ( Container $container ) {
				return new RegistrationRegistry(
					$container->get( PostTypeRegistrarInterface::class ),
					$container->get( TaxonomyRegistrarInterface::class ),
					$container->get( FieldGroupRegistrarInterface::class ),
				);
			}
		);
	}
}
