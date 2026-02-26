<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Scaffolding\Generators;

use Snowberry\WpMvc\Contracts\ProjectLocatorInterface;
use Snowberry\WpMvc\Contracts\ProjectManifestInterface;
use Snowberry\WpMvc\Contracts\ScaffoldGeneratorInterface;
use Snowberry\WpMvc\Contracts\ScaffoldResult;
use Snowberry\WpMvc\Scaffolding\FileStubRepository;
use Snowberry\WpMvc\Scaffolding\Naming;
use Snowberry\WpMvc\Scaffolding\ScaffoldWriter;

final class MakeTaxonomyGenerator implements ScaffoldGeneratorInterface
{
    public function __construct(
        private ProjectLocatorInterface $project,
        private ProjectManifestInterface $manifest,
        private FileStubRepository $stubs,
        private ScaffoldWriter $writer,
    ) {
    }

    public function generate(string $name, array $options = []): ScaffoldResult
    {
        $force = (bool) ($options['force'] ?? false);

        $slug = strtolower($name);
        $class = Naming::studly($slug);

        $appNamespace = $this->manifest->namespace();

        $pluginRoot = rtrim($this->project->pluginRoot(), '/');
        $domainRoot = $pluginRoot . '/' . $this->manifest->path('domain');

        $result = new ScaffoldResult();

        $context = [
            'app_namespace' => $appNamespace,
            'taxonomy' => [
                'slug' => $slug,
                'class' => $class,
            ],
        ];

        $this->writer->writeTemplate(
            $result,
            "{$domainRoot}/PostTypes/{$class}/Generated/{$class}Base.php",
            $this->stubs->get('taxonomy/entity.stub.php'),
            $context,
            $force
        );

        $this->writer->writeTemplate(
            $result,
            "{$domainRoot}/PostTypes/{$class}/Generated/{$class}RepositoryBase.php",
            $this->stubs->get('taxonomy/repository.stub.php'),
            $context,
            $force
        );

        $this->writer->writeTemplate(
            $result,
            "{$domainRoot}/PostTypes/{$class}/{$class}.php",
            $this->stubs->get('taxonomy/entity.concrete.stub.php'),
            $context,
            false
        );

        $this->writer->writeTemplate(
            $result,
            "{$domainRoot}/PostTypes/{$class}/{$class}Repository.php",
            $this->stubs->get('taxonomy/repository.concrete.stub.php'),
            $context,
            false
        );

        return $result;
    }
}
