<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Char_Not_Allowed implements Reason
{
    public function code(): int
    {
        return 1;
    }
    public function description(): string
    {
        return 'Character not allowed';
    }
}