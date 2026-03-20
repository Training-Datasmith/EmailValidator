<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Atext_After_Cfws implements Reason
{
    public function code(): int
    {
        return 133;
    }
    public function description(): string
    {
        return 'ATEXT found after CFWS';
    }
}