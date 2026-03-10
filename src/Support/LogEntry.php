<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Support;

use DateTimeImmutable;

final class LogEntry
{
	private string $level;
	private string $message;
	private array $context;
	private DateTimeImmutable $timestamp;

	public function __construct( string $level, string $message, array $context = [], ?DateTimeImmutable $timestamp = null )
	{
		$this->level = $level;
		$this->message = $message;
		$this->context = $context;
		$this->timestamp = $timestamp ?? new DateTimeImmutable();
	}

	public function level(): string
	{
		return $this->level;
	}

	public function message(): string
	{
		return $this->message;
	}

	public function context(): array
	{
		return $this->context;
	}

	public function timestamp(): DateTimeImmutable
	{
		return $this->timestamp;
	}
}
