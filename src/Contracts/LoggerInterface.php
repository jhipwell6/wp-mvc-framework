<?php

declare(strict_types=1);

namespace Snowberry\WpMvc\Contracts;

interface LoggerInterface
{
	public function debug( string $message, array $context = [] ): void;

	public function info( string $message, array $context = [] ): void;

	public function warning( string $message, array $context = [] ): void;

	public function error( string $message, array $context = [] ): void;

	public function log( string $level, string $message, array $context = [] ): void;
}
