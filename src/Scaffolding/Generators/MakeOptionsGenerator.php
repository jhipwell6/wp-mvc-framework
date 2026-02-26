<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Scaffolding\Generators;

use Snowberry\WpMvc\Contracts\FieldDefinition;
use Snowberry\WpMvc\Contracts\FieldProviderInterface;
use Snowberry\WpMvc\Contracts\ProjectLocatorInterface;
use Snowberry\WpMvc\Contracts\ProjectManifestInterface;
use Snowberry\WpMvc\Contracts\ScaffoldGeneratorInterface;
use Snowberry\WpMvc\Contracts\ScaffoldResult;
use Snowberry\WpMvc\Scaffolding\AcfTypeMapper;
use Snowberry\WpMvc\Scaffolding\FileStubRepository;
use Snowberry\WpMvc\Scaffolding\Naming;
use Snowberry\WpMvc\Scaffolding\ScaffoldWriter;

final class MakeOptionsGenerator implements ScaffoldGeneratorInterface
{
	public function __construct(
		private ProjectLocatorInterface $project,
		private ProjectManifestInterface $manifest,
		private FileStubRepository $stubs,
		private ScaffoldWriter $writer,
		private FieldProviderInterface $fieldProvider,
		private AcfTypeMapper $typeMapper,
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

		[$propertyLines, $hydrationLines, $extractLines] = $this->buildFieldContext($slug, $options);

		$context = [
			'app_namespace' => $appNamespace,
			'options' => [
				'slug' => $slug,
				'class' => $class,
				'prefix' => str_replace('-', '_', $slug),
				'fields' => implode("\n", $propertyLines),
				'field_hydration' => implode("\n", $hydrationLines),
				'field_extract' => implode("\n", $extractLines),
			],
		];

		$result = new ScaffoldResult();

		$this->writer->writeTemplate(
			$result,
			"{$domainRoot}/Options/{$class}/Generated/{$class}Base.php",
			$this->stubs->get('options/entity.stub.php'),
			$context,
			$force
		);

		$this->writer->writeTemplate(
			$result,
			"{$domainRoot}/Options/{$class}/{$class}.php",
			$this->stubs->get('options/entity.concrete.stub.php'),
			$context,
			false
		);

		$this->writer->writeTemplate(
			$result,
			"{$domainRoot}/Options/{$class}/{$class}Repository.php",
			$this->stubs->get('options/repository.concrete.stub.php'),
			$context,
			false
		);

		$result->notes[] = "Generated options model/repository for {$slug}.";

		return $result;
	}

	/**
	 * @return array{0: list<string>, 1: list<string>, 2: list<string>}
	 */
	private function buildFieldContext(string $optionsPage, array $options): array
	{
		$propertyLines = [];
		$hydrationLines = [];
		$extractLines = [];

		foreach ($this->acfFieldsForOptionsPage($optionsPage, $options) as $field) {
			$type = $this->typeMapper->phpType($field);
			$key = $field->name;

			if ($field->type === 'repeater') {
				$propertyLines[] = "\t\tpublic array \${$key} = [],";
				$hydrationLines[] = "\t\t\t{$key}: is_array(\$raw['{$key}'] ?? null) ? \$raw['{$key}'] : [],";
				$extractLines[] = "\t\t\t'{$key}' => \$entity->{$key},";
				continue;
			}

			if ($type === '\\DateTimeImmutable') {
				$propertyLines[] = "\t\tpublic ?\\DateTimeImmutable \${$key} = null,";
				$hydrationLines[] = "\t\t\t{$key}: isset(\$raw['{$key}']) && is_string(\$raw['{$key}']) && \$raw['{$key}'] !== '' ? new \\DateTimeImmutable(\$raw['{$key}']) : null,";
				$extractLines[] = "\t\t\t'{$key}' => \$entity->{$key}?->format(DATE_ATOM),";
				continue;
			}

			$nullable = $field->required ? '' : '?';
			$default = $field->required ? '' : ' = null';
			$propertyLines[] = "\t\tpublic {$nullable}{$type} \${$key}{$default},";
			$hydrationLines[] = "\t\t\t{$key}: \$raw['{$key}'] ?? null,";
			$extractLines[] = "\t\t\t'{$key}' => \$entity->{$key},";
		}

		return [$propertyLines, $hydrationLines, $extractLines];
	}

	/**
	 * @return FieldDefinition[]
	 */
	private function acfFieldsForOptionsPage(string $optionsPage, array $options): array
	{
		if (empty($options['with_acf'])) {
			return [];
		}

		if (! method_exists($this->fieldProvider, 'fieldsForOptionsPage')) {
			return [];
		}

		/** @var callable(string): array $resolver */
		$resolver = [$this->fieldProvider, 'fieldsForOptionsPage'];
		$fields = $resolver($optionsPage);

		if (! is_array($fields)) {
			return [];
		}

		return array_values(
			array_filter(
				$fields,
				static fn (mixed $field): bool => $field instanceof FieldDefinition
			)
		);
	}
}
