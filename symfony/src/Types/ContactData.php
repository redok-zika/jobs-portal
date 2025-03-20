<?php

declare(strict_types=1);

namespace App\Types;

class ContactData
{
    public function __construct(
        public string $name = '',
        public string $email = '',
        public string $phone = ''
    ) {
    }
}
