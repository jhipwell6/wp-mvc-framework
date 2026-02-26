<?php

declare(strict_types=1);

namespace {{ app_namespace }}\Domain\Options\{{ options.class }};

use {{ app_namespace }}\Domain\Persistence\AbstractOptionsRepository;

final class {{ options.class }}Repository extends AbstractOptionsRepository
{
	protected function optionPrefix(): string
	{
		return '{{ options.prefix }}';
	}

	protected function map(array $raw): object
	{
		return new {{ options.class }}(
{{ options.field_hydration }}
		);
	}

	protected function extractData(object $entity): array
	{
		if (! $entity instanceof {{ options.class }}) {
			return [];
		}

		return [
{{ options.field_extract }}
		];
	}
}
