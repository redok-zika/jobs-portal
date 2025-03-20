<?php

declare(strict_types=1);

namespace App\Types;

class JobData
{
    public function __construct(
        public int $jobId = 0,
        public string $title = '',
        public string $description = '',
        public ?bool $active = null,
        /** @var array<AddressData>|null */
        public ?array $addresses = null,
        public ?SalaryData $salary = null,
        /** @var array<WorkfieldData>|null */
        public ?array $workfields = null,
        public ?ContactData $contact = null
    ) {
    }
}
