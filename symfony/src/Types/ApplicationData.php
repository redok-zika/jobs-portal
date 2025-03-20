<?php

declare(strict_types=1);

namespace App\Types;

class ApplicationData
{
  public function __construct(
    public string  $name = '',
    public ?int    $job_id = null,
    public ?string $email = null,
    public ?string $phone = null,
    public ?string $coverLetter = null,
    public bool    $gdprAgreement = false,
    public ?string $linkedin = null,
    public ?string $facebook = null,
    public ?string $twitter = null,
    public string  $source = 'web',
    /** @var array<AttachmentData>|null */
    public ?array  $attachments = null,
    public ?SalaryData $salary = null
  ) {
  }
}