<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Crlf_At_The_End implements Reason
{
    public const CODE = 149;
    public const REASON = 'CRLF at the end';
    public function code(): int
    {
        return 149;
    }
    public function description(): string
    {
        return 'CRLF at the end';
    }
}