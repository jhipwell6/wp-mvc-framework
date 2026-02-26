<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use InvalidArgumentException;
use RuntimeException;
use Snowberry\WpMvc\Contracts\UserDTO;
use Snowberry\WpMvc\Contracts\UserQueryBuilderInterface;
use WP_Error;
use WP_User;
use WP_User_Query;

final class WordPressUserQueryBuilder implements UserQueryBuilderInterface
{
	/**
	 * @var array<string, mixed>
	 */
	private array $queryArgs = [];

	/**
	 * @var array<int, array<string, mixed>>
	 */
	private array $metaQuery = [];

	private const ALLOWED_OPERATORS = [
		'=',
		'!=',
		'>',
		'>=',
		'<',
		'<=',
		'LIKE',
		'NOT LIKE',
		'IN',
		'NOT IN',
		'EXISTS',
		'NOT EXISTS',
	];

	private const EQUALITY_FIELDS = [
		'ID',
		'user_login',
		'user_email',
		'user_nicename',
		'display_name',
	];

	private const ORDERABLE_FIELDS = [
		'ID',
		'login',
		'nicename',
		'email',
		'url',
		'registered',
		'display_name',
		'post_count',
	];

	public function where(string $field, string $operator, mixed $value): UserQueryBuilderInterface
	{
		$normalizedOperator = $this->normalizeOperator($operator);

		if ($normalizedOperator !== '=') {
			throw new InvalidArgumentException('where only supports the "=" operator for core user fields. Use whereMeta for comparison operators.');
		}

		if (!in_array($field, self::EQUALITY_FIELDS, true)) {
			throw new InvalidArgumentException(sprintf('Unsupported where field "%s".', $field));
		}

		switch ($field) {
			case 'ID':
				if (!is_int($value) || $value <= 0) {
					throw new InvalidArgumentException('where value for "ID" must be a positive integer.');
				}
				$this->queryArgs['include'] = [$value];
				break;

			case 'user_login':
			case 'user_email':
			case 'user_nicename':
			case 'display_name':
				if (!is_string($value) || $value === '') {
					throw new InvalidArgumentException(sprintf('where value for "%s" must be a non-empty string.', $field));
				}

				$this->queryArgs['search'] = $value;
				$this->queryArgs['search_columns'] = [$field];
				break;
		}

		return $this;
	}

	public function whereRole(string $role): UserQueryBuilderInterface
	{
		if ($role === '') {
			throw new InvalidArgumentException('Role cannot be empty.');
		}

		$this->queryArgs['role'] = $role;

		return $this;
	}

	public function whereMeta(string $key, string $operator, mixed $value): UserQueryBuilderInterface
	{
		if ($key === '') {
			throw new InvalidArgumentException('Meta key cannot be empty.');
		}

		$normalizedOperator = $this->normalizeOperator($operator);

		$clause = [
			'key' => $key,
			'compare' => $normalizedOperator,
		];

		if ($normalizedOperator !== 'EXISTS' && $normalizedOperator !== 'NOT EXISTS') {
			$clause['value'] = $value;
		}

		$this->metaQuery[] = $clause;

		return $this;
	}

	public function orderBy(string $field, string $direction = 'ASC'): UserQueryBuilderInterface
	{
		if (!in_array($field, self::ORDERABLE_FIELDS, true)) {
			throw new InvalidArgumentException(sprintf('Unsupported orderBy field "%s".', $field));
		}

		$normalizedDirection = strtoupper($direction);
		if ($normalizedDirection !== 'ASC' && $normalizedDirection !== 'DESC') {
			throw new InvalidArgumentException('orderBy direction must be ASC or DESC.');
		}

		$this->queryArgs['orderby'] = $field;
		$this->queryArgs['order'] = $normalizedDirection;

		return $this;
	}

	public function limit(int $limit): UserQueryBuilderInterface
	{
		if ($limit < 1) {
			throw new InvalidArgumentException('limit must be greater than 0.');
		}

		$this->queryArgs['number'] = $limit;

		return $this;
	}

	public function offset(int $offset): UserQueryBuilderInterface
	{
		if ($offset < 0) {
			throw new InvalidArgumentException('offset must be greater than or equal to 0.');
		}

		$this->queryArgs['offset'] = $offset;

		return $this;
	}

	public function get(): array
	{
		$query = $this->executeQuery();
		$results = $query->get_results();

		if ($results instanceof WP_Error) {
			$this->throwWordPressError($results, 'Failed to execute user query.');
		}

		if ($results === []) {
			return [];
		}

		$users = [];
		foreach ($results as $result) {
			if (!$result instanceof WP_User) {
				throw new RuntimeException('Expected WP_User instances from WP_User_Query.');
			}
			$users[] = $this->map($result);
		}

		return $users;
	}

	public function first(): ?UserDTO
	{
		$queryArgs = $this->queryArgs;
		$queryArgs['number'] = 1;
		$query = $this->executeQuery($queryArgs);
		$results = $query->get_results();

		if ($results instanceof WP_Error) {
			$this->throwWordPressError($results, 'Failed to execute user query.');
		}

		if ($results === []) {
			return null;
		}

		$first = $results[0] ?? null;
		if (!$first instanceof WP_User) {
			throw new RuntimeException('Expected a WP_User instance from WP_User_Query.');
		}

		return $this->map($first);
	}

	public function ids(): array
	{
		$queryArgs = $this->queryArgs;
		$queryArgs['fields'] = 'ID';
		$query = $this->executeQuery($queryArgs);
		$results = $query->get_results();

		if ($results instanceof WP_Error) {
			$this->throwWordPressError($results, 'Failed to execute user id query.');
		}

		$ids = [];
		foreach ($results as $result) {
			if (!is_int($result) && !is_string($result)) {
				throw new RuntimeException('Expected integer user IDs from WP_User_Query ids query.');
			}
			$ids[] = (int) $result;
		}

		return $ids;
	}

	/**
	 * @param array<string, mixed>|null $queryArgs
	 */
	private function executeQuery(?array $queryArgs = null): WP_User_Query
	{
		$args = $queryArgs ?? $this->queryArgs;

		if ($this->metaQuery !== []) {
			$args['meta_query'] = [
				'relation' => 'AND',
				...$this->metaQuery,
			];
		}

		return new WP_User_Query($args);
	}

	private function map(WP_User $user): UserDTO
	{
		$roles = array_values(array_map('strval', $user->roles));

		$caps = [];
		foreach ($user->caps as $capability => $allowed) {
			$caps[(string) $capability] = (bool) $allowed;
		}

		return new UserDTO(
			ID: (int) $user->ID,
			user_login: (string) $user->user_login,
			user_email: (string) $user->user_email,
			display_name: (string) $user->display_name,
			roles: $roles,
			caps: $caps,
		);
	}

	private function normalizeOperator(string $operator): string
	{
		$normalizedOperator = strtoupper(trim($operator));

		if (!in_array($normalizedOperator, self::ALLOWED_OPERATORS, true)) {
			throw new InvalidArgumentException(sprintf('Unsupported operator "%s".', $operator));
		}

		return $normalizedOperator;
	}

	private function throwWordPressError(WP_Error $error, string $message): never
	{
		throw new RuntimeException(sprintf('%s %s', $message, $error->get_error_message()));
	}
}
