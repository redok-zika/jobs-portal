<?php

declare(strict_types=1);

namespace App\Types;

class WorkfieldData
{
  public function __construct(
    public int $id = 0,
    public string $name = ''
  ) {
  }
}