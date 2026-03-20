<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Domain_Too_Long implements Reason
{
    public function code(): int
    {
        return 244;
    }
    public function description(): string
    {
        return 'Domain is longer than 253 characters';
    }
}