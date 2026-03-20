<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Unclosed_Comment implements Reason
{
    public function code(): int
    {
        return 146;
    }
    public function description(): string
    {
        return 'No closing comment token found';
    }
}