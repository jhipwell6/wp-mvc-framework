<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface RoleManagerInterface
{

	public function create( string $role, string $label, array $caps = [] ): void;

	public function delete( string $role ): void;

	public function addCapability( string $role, string $cap ): void;

	public function removeCapability( string $role, string $cap ): void;

	public function hasCapability( string $role, string $cap ): bool;
}
