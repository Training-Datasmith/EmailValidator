<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Cr_No_Lf implements Reason
{
    public function code(): int
    {
        return 150;
    }
    public function description(): string
    {
        return 'Missing LF after CR';
    }
}