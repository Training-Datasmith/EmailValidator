<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Validation;

class Dns_Records
{
    /**
     * @param list<array<array-key, mixed>> $records
     */
    public function __construct(private readonly array $records, private readonly bool $error = false)
    {
    }
    /**
     * @return list<array<array-key, mixed>>
     */
    public function get_records(): array
    {
        return $this->records;
    }
    public function with_error(): bool
    {
        return $this->error;
    }
}