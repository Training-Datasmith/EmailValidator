<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Comma_In_Domain implements Reason
{
    public function code(): int
    {
        return 200;
    }
    public function description(): string
    {
        return "Comma ',' is not allowed in domain part";
    }
}