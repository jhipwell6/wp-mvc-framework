<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\CLI;

use Snowberry\WpMvc\Scaffolding\Generators\MakeUserGenerator;

final class MakeUserCommand extends AbstractCommand
{
	public function __construct(
		private MakeUserGenerator $generator,
	) {
	}

	public function name(): string
	{
		return 'wp-mvc make:user';
	}

	public function description(): string
	{
		return 'Generate scaffolding for a user domain model (entity and repository).';
	}

	/**
	 * @param array<int, string> $args
	 * @param array<string, mixed> $assocArgs
	 */
	public function handle(array $args, array $assocArgs): void
	{
		$slug = trim((string) ($args[0] ?? ''));

		if ($slug === '' || ! preg_match('/^[a-z0-9_\-]+$/', $slug)) {
			$this->error('Usage: wp wp-mvc make:user <slug> [--force]');
			return;
		}

		$result = $this->generator->generate($slug, [
			'force' => isset($assocArgs['force']),
		]);

		foreach ($result->created as $path) {
			$this->log("Created: {$path}");
		}

		foreach ($result->skipped as $path) {
			$this->log("Skipped: {$path}");
		}

		foreach ($result->notes as $note) {
			$this->log($note);
		}

		$this->success("Scaffold complete for user '{$slug}'.");
	}
}
