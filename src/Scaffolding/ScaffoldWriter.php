<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Scaffolding;

use Snowberry\WpMvc\Contracts\FilesystemInterface;
use Snowberry\WpMvc\Contracts\TemplateRendererInterface;
use Snowberry\WpMvc\Contracts\ScaffoldResult;

final class ScaffoldWriter
{

	public function __construct(
		private FilesystemInterface $fs,
		private TemplateRendererInterface $renderer,
	)
	{
		
	}

	/**
	 * @param array<string, mixed> $context
	 */
	public function writeTemplate(
		ScaffoldResult $result,
		string $path,
		string $template,
		array $context,
		bool $overwrite = false
	): void
	{
		if ( $this->fs->exists( $path ) && ! $overwrite ) {
			$result->skipped[] = $path;
			return;
		}

		$contents = $this->renderer->render( $template, $context );
		$this->fs->write( $path, $contents, $overwrite );
		$result->created[] = $path;
	}
}
