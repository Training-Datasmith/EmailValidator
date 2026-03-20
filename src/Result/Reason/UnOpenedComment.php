<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Un_Opened_Comment implements Reason
{
    public function code(): int
    {
        return 152;
    }
    public function description(): string
    {
        return 'Missing opening comment parentheses - https://tools.ietf.org/html/rfc5322#section-3.2.2';
    }
}