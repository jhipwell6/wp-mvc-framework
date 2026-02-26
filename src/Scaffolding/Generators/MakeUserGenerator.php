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

final class MakeUserGenerator implements ScaffoldGeneratorInterface
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

		$pluginRoot = rtrim($this->project->pluginRoot(), '/');
		$domainRoot = $pluginRoot . '/' . $this->manifest->path('domain');
		$appNamespace = $this->manifest->namespace();

		$context = [
			'app_namespace' => $appNamespace,
			'user' => [
				'slug' => $slug,
				'class' => $class,
			],
		];

		$result = new ScaffoldResult();

		$this->writer->writeTemplate(
			$result,
			"{$domainRoot}/Users/{$class}/Generated/{$class}Base.php",
			$this->stubs->get('user/entity.stub.php'),
			$context,
			$force
		);

		$this->writer->writeTemplate(
			$result,
			"{$domainRoot}/Users/{$class}/{$class}.php",
			$this->stubs->get('user/entity.concrete.stub.php'),
			$context,
			false
		);

		$this->writer->writeTemplate(
			$result,
			"{$domainRoot}/Users/{$class}/{$class}Repository.php",
			$this->stubs->get('user/repository.concrete.stub.php'),
			$context,
			false
		);

		$result->notes[] = "Generated user model/repository for {$slug}.";

		return $result;
	}
}
