<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Empty_Reason implements Reason
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