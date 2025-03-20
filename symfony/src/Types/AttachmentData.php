<?php

declare(strict_types=1);

namespace App\Types;

class AttachmentData
{
  public function __construct(
    public string $filename = '',
    public int $type = 1,
    public ?string $path = null,
    public ?string $base64 = null
  ) {
  }
}