<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Rfc_Warnings implements Reason
{
    public function code(): int
    {
        return 997;
    }
    public function description(): string
    {
        return 'Warnings found after validating';
    }
}