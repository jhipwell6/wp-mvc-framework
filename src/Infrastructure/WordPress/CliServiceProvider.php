<?php
declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\Core\Container;
use Snowberry\WpMvc\Core\ServiceProvider;
use Snowberry\WpMvc\CLI\CommandRegistry;
use Snowberry\WpMvc\CLI\MakePostTypeCommand;
use Snowberry\WpMvc\Cli\Commands\MakeTaxonomyCommand;
use Snowberry\WpMvc\Contracts\FilesystemInterface;
use Snowberry\WpMvc\Contracts\ProjectLocatorInterface;
use Snowberry\WpMvc\Contracts\ProjectManifestInterface;
use Snowberry\WpMvc\Contracts\TemplateRendererInterface;
use Snowberry\WpMvc\Contracts\ScaffoldGeneratorInterface;
use Snowberry\WpMvc\Infrastructure\LocalFilesystem;
use Snowberry\WpMvc\Infrastructure\SimpleTemplateRenderer;
use Snowberry\WpMvc\Scaffolding\FileStubRepository;
use Snowberry\WpMvc\Scaffolding\ScaffoldWriter;
use Snowberry\WpMvc\Scaffolding\Generators\MakePostTypeGenerator;
use Snowberry\WpMvc\Scaffolding\Generators\MakeTaxonomyGenerator;

final class CliServiceProvider extends ServiceProvider
{
    public function register(Container $container): void
    {
        // WP-CLI command registry
        $container->singleton(CommandRegistry::class, fn () => new CommandRegistry());

        // Scaffolding engine services
        $container->singleton(FilesystemInterface::class, fn () => new LocalFilesystem());
        $container->singleton(TemplateRendererInterface::class, fn () => new SimpleTemplateRenderer());
        $container->singleton(ProjectLocatorInterface::class, fn () => new ActivePluginProjectLocator());

        // Manifest (client plugin root/wp-mvc.php)
        $container->singleton(ProjectManifestInterface::class, function (Container $c) {
            return new PhpProjectManifest(
                $c->get(ProjectLocatorInterface::class)
            );
        });

        // Stubs + writer
        $container->singleton(FileStubRepository::class, fn () => new FileStubRepository());

        $container->singleton(ScaffoldWriter::class, function (Container $c) {
            return new ScaffoldWriter(
                $c->get(FilesystemInterface::class),
                $c->get(TemplateRendererInterface::class),
            );
        });

        // Generator binding (post type)
        $container->singleton(ScaffoldGeneratorInterface::class, function (Container $c) {
            return new MakePostTypeGenerator(
                $c->get(ProjectLocatorInterface::class),
                $c->get(ProjectManifestInterface::class),
                $c->get(FileStubRepository::class),
                $c->get(ScaffoldWriter::class),
            );
        });

        // Generator binding (taxonomy)
        $container->singleton(MakeTaxonomyGenerator::class, function (Container $c) {
            return new MakeTaxonomyGenerator(
                $c->get(ProjectLocatorInterface::class),
                $c->get(ProjectManifestInterface::class),
                $c->get(FileStubRepository::class),
                $c->get(ScaffoldWriter::class),
                $c->get(\Snowberry\WpMvc\Contracts\FieldProviderInterface::class),
                $c->get(\Snowberry\WpMvc\Scaffolding\AcfTypeMapper::class),
            );
        });
    }

    public function boot(Container $container): void
    {
        if (!defined('WP_CLI') || !\WP_CLI) {
            return;
        }

        $registry = $container->get(CommandRegistry::class);

        $registry->add(new MakePostTypeCommand(
            $container->get(ScaffoldGeneratorInterface::class)
        ));

        $registry->add(new MakeTaxonomyCommand($container));

        $registry->registerAll();
    }
}