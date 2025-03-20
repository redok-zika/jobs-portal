<?php

declare(strict_types=1);

namespace App\Types;

class JobFilters
{
    public function __construct(
        public ?string $workfield = null,
        public ?string $officeId = null,
        public ?string $channelAssignation = null
    ) {
    }
}
