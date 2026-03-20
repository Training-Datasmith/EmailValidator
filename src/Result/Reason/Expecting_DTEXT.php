<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Expecting_Dtext implements Reason
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