<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Domain_Hyphened extends Detailed_Reason
{
    public function code(): int
    {
        return 144;
    }
    public function description(): string
    {
        return 'S_HYPHEN found in domain';
    }
}