<?php

declare(strict_types=1);

namespace App\Types;

class SalaryData
{
  public function __construct(
    public int $amount = 0,
    public string $currency = 'CZK',
    public string $unit = 'month',
    public int $type = 0,
    public ?string $note = null,
    public bool $visible = true
  ) {
  }
}