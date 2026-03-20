<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Unclosed_Quoted_String implements Reason
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