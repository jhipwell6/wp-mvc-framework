<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Builders;

use Snowberry\WpMvc\Contracts\PostTypeDefinition;

final class PostTypeBuilder
{
	private string $slug;
	private array $args = [];

	private function __construct( string $slug )
	{
		$this->slug = $slug;
	}

	public static function make( string $slug ): self
	{
		return new self( $slug );
	}

	public function label( string $label ): self
	{
		$this->args['label'] = $label;
		return $this;
	}

	public function labels( array $labels ): self
	{
		$this->args['labels'] = $labels;
		return $this;
	}

	public function public( bool $value = true ): self
	{
		$this->args['public'] = $value;
		return $this;
	}

	public function supports( array $supports ): self
	{
		$this->args['supports'] = $supports;
		return $this;
	}

	public function rest( bool $enabled = true ): self
	{
		$this->args['show_in_rest'] = $enabled;
		return $this;
	}

	public function archive( bool|string $value = true ): self
	{
		$this->args['has_archive'] = $value;
		return $this;
	}

	public function rewrite( array|bool $rewrite ): self
	{
		$this->args['rewrite'] = $rewrite;
		return $this;
	}

	public function capabilityType( string $type ): self
	{
		$this->args['capability_type'] = $type;
		return $this;
	}

	public function args( array $args ): self
	{
		$this->args = array_merge( $this->args, $args );
		return $this;
	}

	public function build(): PostTypeDefinition
	{
		return new PostTypeDefinition(
			$this->slug,
			$this->args
		);
	}
}
