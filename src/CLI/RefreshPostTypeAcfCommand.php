<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\CLI;

use Snowberry\WpMvc\Scaffolding\Generators\MakePostTypeGenerator;

final class RefreshPostTypeAcfCommand extends AbstractCommand
{
	public function __construct(
		private MakePostTypeGenerator $generator,
	) {
	}

	public function name(): string
	{
		return 'wp-mvc refresh:post-type-acf';
	}

	public function description(): string
	{
		return 'Refresh generated post type model bases from the latest ACF field definitions.';
	}

	/**
	 * @param array<int, string> $args
	 * @param array<string, mixed> $assocArgs
	 */
	public function handle(array $args, array $assocArgs): void
	{
		$slug = trim((string) ($args[0] ?? ''));
		if ($slug === '' || ! preg_match('/^[a-z0-9_\-]+$/', $slug)) {
			$this->error('Usage: wp wp-mvc refresh:post-type-acf <slug>');
			return;
		}

		$result = $this->generator->refreshFromAcf($slug, true);

		foreach ($result->created as $path) {
			$this->log("Created: {$path}");
		}

		foreach ($result->skipped as $path) {
			$this->log("Skipped: {$path}");
		}

		foreach ($result->notes as $note) {
			$this->log($note);
		}

		$this->success("ACF refresh complete for post type '{$slug}'.");
	}
}
