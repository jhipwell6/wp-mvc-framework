<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Contracts\AssetManagerInterface;
use Snowberry\WpMvc\Support\Asset;
use Snowberry\WpMvc\Support\AssetContext;

class WordPressAssetLoader
{

	public function __construct(
		private AssetManagerInterface $assets
	)
	{
		
	}

	public function registerHooks(): void
	{
		add_action( 'wp_enqueue_scripts', fn() => $this->enqueueFor( AssetContext::FRONTEND ) );
		add_action( 'admin_enqueue_scripts', fn() => $this->enqueueFor( AssetContext::ADMIN ) );

		// Block editor only
		add_action( 'enqueue_block_editor_assets', fn() => $this->enqueueFor( AssetContext::EDITOR ) );

		// Both editor + frontend (useful for shared block styles/scripts)
		add_action( 'enqueue_block_assets', fn() => $this->enqueueFor( AssetContext::BLOCK ) );

		add_action( 'login_enqueue_scripts', fn() => $this->enqueueFor( AssetContext::LOGIN ) );
	}

	private function enqueueFor( string $context ): void
	{
		foreach ( $this->assets->all() as $asset ) {
			if ( ! $asset->appliesTo( $context ) ) {
				continue;
			}

			$this->enqueueAsset( $asset );
		}
	}

	private function enqueueAsset( Asset $asset ): void
	{
		if ( $asset->isScript() ) {
			wp_enqueue_script(
				$asset->handle(),
				$asset->src(),
				$asset->deps(),
				$asset->version(),
				$asset->inFooter()
			);
			return;
		}

		if ( $asset->isStyle() ) {
			wp_enqueue_style(
				$asset->handle(),
				$asset->src(),
				$asset->deps(),
				$asset->version()
			);
		}
	}
}
