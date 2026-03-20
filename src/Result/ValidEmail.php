<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result;

class Valid_Email implements Result
{
    public function is_valid(): bool
    {
        return true;
    }
    public function is_invalid(): bool
    {
        return false;
    }
    public function description(): string
    {
        return 'Valid email';
    }
    public function code(): int
    {
        return 0;
    }
}