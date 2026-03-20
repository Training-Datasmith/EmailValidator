<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Consecutive_Dot implements Reason
{
    public function code(): int
    {
        return 132;
    }
    public function description(): string
    {
        return 'Concecutive DOT found';
    }
}