<?php

declare(strict_types=1);

namespace Egulias\EmailValidator\Result\Reason;

class LabelTooLong implements Reason
{
    public function code(): int
    {
        return 245;
    }

    public function description(): string
    {
        return 'Domain "label" is longer than 63 characters';
    }
}
