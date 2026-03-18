<?php

declare(strict_types=1);

namespace Egulias\EmailValidator\Result\Reason;

class ExpectingDTEXT implements Reason
{
    public function code(): int
    {
        return 129;
    }

    public function description(): string
    {
        return 'Expecting DTEXT';
    }
}
