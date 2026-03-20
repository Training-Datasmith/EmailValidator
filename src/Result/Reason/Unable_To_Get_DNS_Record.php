<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

/**
 * Used on SERVFAIL, TIMEOUT or other runtime and network errors
 */
class Unable_To_Get_Dns_Record extends No_Dns_Record
{
    public function code(): int
    {
        return 3;
    }
    public function description(): string
    {
        return 'Unable to get DNS records for the host';
    }
}