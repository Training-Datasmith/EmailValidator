<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Expecting_Atext extends Detailed_Reason
{
    public function code(): int
    {
        return 137;
    }
    public function description(): string
    {
        return 'Expecting ATEXT (Printable US-ASCII). Extended: ' . $this->detailed_description;
    }
}