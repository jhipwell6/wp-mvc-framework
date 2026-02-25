<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface RegistrationRegistryInterface
{

	public function addPostType( PostTypeDefinition $definition ): void;

	public function addTaxonomy( TaxonomyDefinition $definition ): void;

	public function addFieldGroup( array $group ): void;

	public function registerAll(): void;
}
