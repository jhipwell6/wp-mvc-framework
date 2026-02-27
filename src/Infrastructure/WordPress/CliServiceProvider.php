<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use Snowberry\WpMvc\CLI\CommandRegistry;
use Snowberry\WpMvc\CLI\MakePostTypeCommand;
use Snowberry\WpMvc\CLI\MakeTaxonomyCommand;
use Snowberry\WpMvc\CLI\MakeUserCommand;
use Snowberry\WpMvc\CLI\RefreshPostTypeAcfCommand;
use Snowberry\WpMvc\Contracts\FilesystemInterface;
use Snowberry\WpMvc\Contracts\ProjectLocatorInterface;
use Snowberry\WpMvc\Contracts\ProjectManifestInterface;
use Snowberry\WpMvc\Contracts\ScaffoldGeneratorInterface;
use Snowberry\WpMvc\Contracts\TemplateRendererInterface;
use Snowberry\WpMvc\Core\Container;
use Snowberry\WpMvc\Core\ServiceProvider;
use Snowberry\WpMvc\Infrastructure\LocalFilesystem;
use Snowberry\WpMvc\Infrastructure\SimpleTemplateRenderer;
use Snowberry\WpMvc\Scaffolding\FileStubRepository;
use Snowberry\WpMvc\Scaffolding\Generators\MakePostTypeGenerator;
use Snowberry\WpMvc\Scaffolding\Generators\MakeTaxonomyGenerator;
use Snowberry\WpMvc\Scaffolding\Generators\MakeUserGenerator;
use Snowberry\WpMvc\Scaffolding\ScaffoldWriter;

final class CliServiceProvider extends ServiceProvider
{
	public function register(Container $container): void
	{
		$container->singleton(CommandRegistry::class, fn (): CommandRegistry => new CommandRegistry());

		$container->singleton(FilesystemInterface::class, fn (): FilesystemInterface => new LocalFilesystem());
		$container->singleton(TemplateRendererInterface::class, fn (): TemplateRendererInterface => new SimpleTemplateRenderer());
		$container->singleton(ProjectLocatorInterface::class, fn (): ProjectLocatorInterface => new ActivePluginProjectLocator());

		$container->singleton(ProjectManifestInterface::class, function (Container $c): ProjectManifestInterface {
			return new PhpProjectManifest($c->get(ProjectLocatorInterface::class));
		});

		$container->singleton(FileStubRepository::class, fn (): FileStubRepository => new FileStubRepository());
		$container->singleton(ScaffoldWriter::class, function (Container $c): ScaffoldWriter {
			return new ScaffoldWriter(
				$c->get(FilesystemInterface::class),
				$c->get(TemplateRendererInterface::class),
			);
		});

		$container->singleton(MakePostTypeGenerator::class, function (Container $c): MakePostTypeGenerator {
			return new MakePostTypeGenerator(
				$c->get(ProjectLocatorInterface::class),
				$c->get(ProjectManifestInterface::class),
				$c->get(FileStubRepository::class),
				$c->get(ScaffoldWriter::class),
				$c->get(\Snowberry\WpMvc\Contracts\FieldProviderInterface::class),
				$c->get(\Snowberry\WpMvc\Scaffolding\AcfTypeMapper::class),
			);
		});

		$container->singleton(ScaffoldGeneratorInterface::class, fn (Container $c): ScaffoldGeneratorInterface => $c->get(MakePostTypeGenerator::class));

		$container->singleton(MakeTaxonomyGenerator::class, function (Container $c): MakeTaxonomyGenerator {
			return new MakeTaxonomyGenerator(
				$c->get(ProjectLocatorInterface::class),
				$c->get(ProjectManifestInterface::class),
				$c->get(FileStubRepository::class),
				$c->get(ScaffoldWriter::class),
				$c->get(\Snowberry\WpMvc\Contracts\FieldProviderInterface::class),
				$c->get(\Snowberry\WpMvc\Scaffolding\AcfTypeMapper::class),
			);
		});

		$container->singleton(MakeUserGenerator::class, function (Container $c): MakeUserGenerator {
			return new MakeUserGenerator(
				$c->get(ProjectLocatorInterface::class),
				$c->get(ProjectManifestInterface::class),
				$c->get(FileStubRepository::class),
				$c->get(ScaffoldWriter::class),
			);
		});
	}

	public function boot(Container $container): void
	{
		if (! defined('WP_CLI') || ! \WP_CLI) {
			return;
		}

		$registry = $container->get(CommandRegistry::class);

		$registry->add(new MakePostTypeCommand($container->get(ScaffoldGeneratorInterface::class)));
		$registry->add(new RefreshPostTypeAcfCommand($container->get(MakePostTypeGenerator::class)));
		$registry->add(new MakeTaxonomyCommand($container));
		$registry->add(new MakeUserCommand($container->get(MakeUserGenerator::class)));

		$registry->registerAll();
	}
}
