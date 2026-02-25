<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

final class ScaffoldResult
{

	/**
	 * @param string[] $created
	 * @param string[] $skipped
	 */
	public function __construct(
		public array $created = [],
		public array $skipped = [],
		public array $notes = [],
	)
	{
		
	}
}
