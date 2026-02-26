<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use RuntimeException;
use Snowberry\WpMvc\Contracts\AuthorizationException;
use Snowberry\WpMvc\Contracts\PermissionCheckerInterface;
use WP_User;

final class WordPressPermissionChecker implements PermissionCheckerInterface
{

	public function can( string $capability, int $userId = 0 ): bool
	{
		if ( $userId === 0 ) {
			return current_user_can( $capability );
		}

		return user_can( $userId, $capability );
	}

	public function cannot( string $capability, int $userId = 0 ): bool
	{
		return ! $this->can( $capability, $userId );
	}

	public function require( string $capability, int $userId = 0 ): void
	{
		if ( $this->cannot( $capability, $userId ) ) {
			throw new AuthorizationException( $capability, 'capability', $this->resolveUserId( $userId ) );
		}
	}

	public function hasRole( string $role, int $userId = 0 ): bool
	{
		$user = $this->resolveUser( $userId );

		return in_array( $role, $user->roles, true );
	}

	public function requireRole( string $role, int $userId = 0 ): void
	{
		if ( ! $this->hasRole( $role, $userId ) ) {
			throw new AuthorizationException( $role, 'role', $this->resolveUserId( $userId ) );
		}
	}

	public function hasAnyRole( array $roles, int $userId = 0 ): bool
	{
		$user = $this->resolveUser( $userId );

		foreach ( $roles as $role ) {
			if ( in_array( $role, $user->roles, true ) ) {
				return true;
			}
		}

		return false;
	}

	public function hasAllRoles( array $roles, int $userId = 0 ): bool
	{
		$user = $this->resolveUser( $userId );

		foreach ( $roles as $role ) {
			if ( ! in_array( $role, $user->roles, true ) ) {
				return false;
			}
		}

		return true;
	}

	private function resolveUser( int $userId ): WP_User
	{
		$resolvedUserId = $this->resolveUserId( $userId );
		$user = get_userdata( $resolvedUserId );

		if ( ! $user instanceof WP_User ) {
			throw new RuntimeException( sprintf( 'Unable to resolve WordPress user %d.', $resolvedUserId ) );
		}

		return $user;
	}

	private function resolveUserId( int $userId ): int
	{
		if ( $userId !== 0 ) {
			return $userId;
		}

		$currentUserId = get_current_user_id();
		if ( $currentUserId <= 0 ) {
			throw new RuntimeException( 'No current WordPress user is authenticated.' );
		}

		return $currentUserId;
	}
}
