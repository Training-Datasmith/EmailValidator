<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class No_Domain_Part implements Reason
{
    public function code(): int
    {
        return 131;
    }
    public function description(): string
    {
        return 'No domain part found';
    }
}