<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use RuntimeException;
use Snowberry\WpMvc\Contracts\RoleManagerInterface;

final class WordPressRoleManager implements RoleManagerInterface
{

	public function create( string $role, string $label, array $caps = [] ): void
	{
		$createdRole = add_role( $role, $label, $caps );

		if ( $createdRole === null ) {
			throw new RuntimeException( sprintf( 'Failed to create role "%s".', $role ) );
		}
	}

	public function delete( string $role ): void
	{
		$this->getExistingRole( $role );
		remove_role( $role );
	}

	public function addCapability( string $role, string $cap ): void
	{
		$this->getExistingRole( $role )->add_cap( $cap );
	}

	public function removeCapability( string $role, string $cap ): void
	{
		$this->getExistingRole( $role )->remove_cap( $cap );
	}

	public function hasCapability( string $role, string $cap ): bool
	{
		return $this->getExistingRole( $role )->has_cap( $cap );
	}

	private function getExistingRole( string $role ): \WP_Role
	{
		$wpRole = get_role( $role );

		if ( $wpRole === null ) {
			throw new RuntimeException( sprintf( 'Role "%s" does not exist.', $role ) );
		}

		return $wpRole;
	}
}
