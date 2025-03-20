<?php

declare(strict_types=1);

namespace App\Types;

class JobResponse
{
    public function __construct(
        public array $payload = [],
        public array $meta = []
    ) {
    }
}
