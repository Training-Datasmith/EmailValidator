<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class No_Dns_Record implements Reason
{
    public function code(): int
    {
        return 5;
    }
    public function description(): string
    {
        return 'No MX or A DNS record was found for this email';
    }
}