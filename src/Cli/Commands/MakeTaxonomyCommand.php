<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Cli\Commands;

use RuntimeException;
use Snowberry\WpMvc\Core\Container;
use Snowberry\WpMvc\Scaffolding\Generators\MakeTaxonomyGenerator;

final class MakeTaxonomyCommand
{
    public function __construct(
        private Container $container,
    ) {
    }

    public function name(): string
    {
        return 'wp-mvc make:taxonomy';
    }

    public function description(): string
    {
        return 'Scaffold a new taxonomy with generated base and concrete classes.';
    }

    /**
     * @param array<int, string> $args
     * @param array<string, mixed> $assocArgs
     */
    public function handle(array $args, array $assocArgs): void
    {
        $slug = trim((string) ($args[0] ?? ''));

        if ($slug === '' || ! preg_match('/^[a-z0-9_\-]+$/', $slug)) {
            \WP_CLI::error('Usage: wp wp-mvc make:taxonomy <slug> [--force]');
            return;
        }

        $force = isset($assocArgs['force']);

        try {
            $generator = $this->container->get(MakeTaxonomyGenerator::class);
            $generator->generate($slug, ['force' => $force]);
        } catch (RuntimeException $e) {
            \WP_CLI::error($e->getMessage());
            return;
        }

        \WP_CLI::success(sprintf("Scaffold complete for taxonomy '%s'.", $slug));
    }
}
