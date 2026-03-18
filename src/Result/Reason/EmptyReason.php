<?php

declare(strict_types=1);

namespace Egulias\EmailValidator\Result\Reason;

class EmptyReason implements Reason
{
    public function code(): int
    {
        return 0;
    }

    public function description(): string
    {
        return 'Empty reason';
    }
}
