<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Domain_Accepts_No_Mail implements Reason
{
    public function code(): int
    {
        return 154;
    }
    public function description(): string
    {
        return 'Domain accepts no mail (Null MX, RFC7505)';
    }
}