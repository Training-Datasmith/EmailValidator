<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Local_Or_Reserved_Domain implements Reason
{
    public function code(): int
    {
        return 153;
    }
    public function description(): string
    {
        return 'Local, mDNS or reserved domain (RFC2606, RFC6762)';
    }
}