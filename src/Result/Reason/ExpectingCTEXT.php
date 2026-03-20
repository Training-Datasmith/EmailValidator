<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Expecting_Ctext implements Reason
{
    public function code(): int
    {
        return 139;
    }
    public function description(): string
    {
        return 'Expecting CTEXT';
    }
}