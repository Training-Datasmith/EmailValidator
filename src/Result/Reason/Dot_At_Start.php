<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Dot_At_Start implements Reason
{
    public function code(): int
    {
        return 141;
    }
    public function description(): string
    {
        return 'Starts with a DOT';
    }
}