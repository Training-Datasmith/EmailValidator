<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Expecting_Domain_Literal_Close implements Reason
{
    public function code(): int
    {
        return 137;
    }
    public function description(): string
    {
        return "Closing bracket ']' for domain literal not found";
    }
}