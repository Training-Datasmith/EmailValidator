<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Consecutive_At implements Reason
{
    public function code(): int
    {
        return 128;
    }
    public function description(): string
    {
        return '@ found after another @';
    }
}