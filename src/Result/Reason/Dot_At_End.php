<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Dot_At_End implements Reason
{
    public function code(): int
    {
        return 142;
    }
    public function description(): string
    {
        return 'Dot at the end';
    }
}