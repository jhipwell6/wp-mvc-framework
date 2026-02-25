<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Builders;

use Snowberry\WpMvc\Contracts\TaxonomyDefinition;

final class TaxonomyBuilder
{
	private string $slug;
	private array $postTypes = [];
	private array $args = [];

	private function __construct( string $slug )
	{
		$this->slug = $slug;
	}

	public static function make( string $slug ): self
	{
		return new self( $slug );
	}

	public function for( array|string $postTypes ): self
	{
		$this->postTypes = (array) $postTypes;
		return $this;
	}

	public function label( string $label ): self
	{
		$this->args['label'] = $label;
		return $this;
	}

	public function hierarchical( bool $value = true ): self
	{
		$this->args['hierarchical'] = $value;
		return $this;
	}

	public function rest( bool $enabled = true ): self
	{
		$this->args['show_in_rest'] = $enabled;
		return $this;
	}

	public function rewrite( array|bool $rewrite ): self
	{
		$this->args['rewrite'] = $rewrite;
		return $this;
	}

	public function args( array $args ): self
	{
		$this->args = array_merge( $this->args, $args );
		return $this;
	}

	public function build(): TaxonomyDefinition
	{
		return new TaxonomyDefinition(
			$this->slug,
			$this->postTypes,
			$this->args
		);
	}
}
