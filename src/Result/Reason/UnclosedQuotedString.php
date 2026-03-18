<?php

declare(strict_types=1);

namespace Egulias\EmailValidator\Result\Reason;

class UnclosedQuotedString implements Reason
{
    public function code(): int
    {
        return 145;
    }

    public function description(): string
    {
        return 'Unclosed quoted string';
    }
}
