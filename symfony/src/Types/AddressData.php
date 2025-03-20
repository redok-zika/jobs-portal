<?php

declare(strict_types=1);

namespace App\Types;

class AddressData
{
  public function __construct(
    public string $city = '',
    public string $region = '',
    public ?int $id = null,
    public ?int $postcode = null,
    public ?string $street = null,
    public ?string $state = null,
    public ?bool $isPrimary = null
  ) {
  }
}