<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Spoof_Email implements Reason
{
    public function code(): int
    {
        return 298;
    }
    public function description(): string
    {
        return 'The email contains mixed UTF8 chars that makes it suspicious';
    }
}