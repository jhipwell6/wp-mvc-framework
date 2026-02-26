<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Infrastructure\WordPress;

use InvalidArgumentException;
use Snowberry\WpMvc\Contracts\PostDTO;
use Snowberry\WpMvc\Contracts\PostQueryBuilderInterface;
use WP_Post;
use WP_Query;

final class WordPressPostQueryBuilder implements PostQueryBuilderInterface
{
	/**
	 * @var array<string, mixed>
	 */
	private array $queryArgs = [
		'post_status' => 'any',
	];

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

	private const ORDERABLE_FIELDS = [
		'ID',
		'author',
		'title',
		'name',
		'type',
		'date',
		'modified',
		'parent',
		'rand',
		'menu_order',
	];

	private const EQUALITY_FIELDS = [
		'ID',
		'post_type',
		'post_status',
		'post_name',
		'post_parent',
		'post_author',
	];

	private const IN_FIELDS = [
		'ID',
		'post_type',
		'post_status',
		'post_parent',
		'post_author',
	];

	public function where(string $field, string $operator, mixed $value): PostQueryBuilderInterface
	{
		$normalizedOperator = $this->normalizeOperator($operator);

		if ($normalizedOperator !== '=') {
			throw new InvalidArgumentException('where only supports the "=" operator for core post fields. Use whereMeta for comparison operators.');
		}

		if (!in_array($field, self::EQUALITY_FIELDS, true)) {
			throw new InvalidArgumentException(sprintf('Unsupported where field "%s".', $field));
		}

		switch ($field) {
			case 'ID':
				$this->assertPositiveInt($value, 'ID');
				$this->queryArgs['p'] = $value;
				break;

			case 'post_parent':
			case 'post_author':
				$this->assertNonNegativeInt($value, $field);
				$this->queryArgs[$field] = $value;
				break;

			case 'post_type':
			case 'post_status':
			case 'post_name':
				if (!is_string($value) || $value === '') {
					throw new InvalidArgumentException(sprintf('where value for "%s" must be a non-empty string.', $field));
				}
				$this->queryArgs[$field] = $value;
				break;
		}

		return $this;
	}

	public function whereMeta(string $key, string $operator, mixed $value): PostQueryBuilderInterface
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

	public function whereIn(string $field, array $values): PostQueryBuilderInterface
	{
		if (!in_array($field, self::IN_FIELDS, true)) {
			throw new InvalidArgumentException(sprintf('Unsupported whereIn field "%s".', $field));
		}

		if ($values === []) {
			throw new InvalidArgumentException('whereIn values cannot be empty.');
		}

		switch ($field) {
			case 'ID':
				$ids = [];
				foreach ($values as $value) {
					$this->assertPositiveInt($value, 'ID');
					$ids[] = $value;
				}
				$this->queryArgs['post__in'] = array_values(array_unique($ids));
				break;

			case 'post_parent':
				$parents = [];
				foreach ($values as $value) {
					$this->assertNonNegativeInt($value, 'post_parent');
					$parents[] = $value;
				}
				$this->queryArgs['post_parent__in'] = array_values(array_unique($parents));
				break;

			case 'post_author':
				$authors = [];
				foreach ($values as $value) {
					$this->assertNonNegativeInt($value, 'post_author');
					$authors[] = $value;
				}
				$this->queryArgs['author__in'] = array_values(array_unique($authors));
				break;

			case 'post_type':
			case 'post_status':
				$strings = [];
				foreach ($values as $value) {
					if (!is_string($value) || $value === '') {
						throw new InvalidArgumentException(sprintf('whereIn values for "%s" must be non-empty strings.', $field));
					}
					$strings[] = $value;
				}
				$this->queryArgs[$field] = array_values(array_unique($strings));
				break;
		}

		return $this;
	}

	public function orderBy(string $field, string $direction = 'ASC'): PostQueryBuilderInterface
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

	public function limit(int $limit): PostQueryBuilderInterface
	{
		if ($limit < 1) {
			throw new InvalidArgumentException('limit must be greater than 0.');
		}

		$this->queryArgs['posts_per_page'] = $limit;

		return $this;
	}

	public function offset(int $offset): PostQueryBuilderInterface
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

		if ($query->posts === []) {
			return [];
		}

		$results = [];
		foreach ($query->posts as $post) {
			if (!$post instanceof WP_Post) {
				throw new InvalidArgumentException('Expected WP_Post instances from WP_Query.');
			}

			$results[] = $this->map($post);
		}

		return $results;
	}

	public function first(): ?PostDTO
	{
		$queryArgs = $this->queryArgs;
		$queryArgs['posts_per_page'] = 1;
		$query = $this->executeQuery($queryArgs);

		if ($query->posts === []) {
			return null;
		}

		$post = $query->posts[0];
		if (!$post instanceof WP_Post) {
			throw new InvalidArgumentException('Expected a WP_Post instance from WP_Query.');
		}

		return $this->map($post);
	}

	public function ids(): array
	{
		$queryArgs = $this->queryArgs;
		$queryArgs['fields'] = 'ids';
		$query = $this->executeQuery($queryArgs);

		$ids = [];
		foreach ($query->posts as $id) {
			if (!is_int($id)) {
				throw new InvalidArgumentException('Expected integer IDs from WP_Query ids query.');
			}
			$ids[] = $id;
		}

		return $ids;
	}

	/**
	 * @param array<string, mixed>|null $queryArgs
	 */
	private function executeQuery(?array $queryArgs = null): WP_Query
	{
		$args = $queryArgs ?? $this->queryArgs;

		if ($this->metaQuery !== []) {
			$args['meta_query'] = $this->metaQuery;
		}

		return new WP_Query($args);
	}

	private function map(WP_Post $post): PostDTO
	{
		return new PostDTO(
			ID: $post->ID,
			post_type: $post->post_type,
			post_title: $post->post_title,
			post_content: $post->post_content,
			post_status: $post->post_status,
			raw: (array) $post
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

	private function assertPositiveInt(mixed $value, string $field): void
	{
		if (!is_int($value) || $value <= 0) {
			throw new InvalidArgumentException(sprintf('Value for "%s" must be a positive integer.', $field));
		}
	}

	private function assertNonNegativeInt(mixed $value, string $field): void
	{
		if (!is_int($value) || $value < 0) {
			throw new InvalidArgumentException(sprintf('Value for "%s" must be a non-negative integer.', $field));
		}
	}
}
