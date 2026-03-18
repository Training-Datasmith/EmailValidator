<?php

declare(strict_types=1);

namespace Egulias\EmailValidator\Result\Reason;

class RFCWarnings implements Reason
{
    public function code(): int
    {
        return 997;
    }

    public function description(): string
    {
        return 'Warnings found after validating';
    }
}
