<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Label_Too_Long implements Reason
{
    public function code(): int
    {
        return 245;
    }
    public function description(): string
    {
        return 'Domain "label" is longer than 63 characters';
    }
}