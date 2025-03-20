<?php

declare(strict_types=1);

namespace App\Types;

class ApiResponse
{
  /**
   * @param array<string, mixed> $payload
   * @param array<string, string> $meta
   */
  public function __construct(
    public array $payload = [],
    public array $meta = []
  ) {
  }
}