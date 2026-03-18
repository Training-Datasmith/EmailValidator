<?php

declare(strict_types=1);

namespace Egulias\EmailValidator\Result\Reason;

class UnusualElements implements Reason
{
    public function __construct(private readonly string $element)
    {
    }

    public function code(): int
    {
        return 201;
    }

    public function description(): string
    {
        return 'Unusual element found, wourld render invalid in majority of cases. Element found: ' . $this->element;
    }
}
