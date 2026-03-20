<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class No_Local_Part implements Reason
{
    public function code(): int
    {
        return 130;
    }
    public function description(): string
    {
        return 'No local part';
    }
}