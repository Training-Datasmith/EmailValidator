<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result;

use Egulias\Email_Validator\Result\Reason\Reason;
class Invalid_Email implements Result
{
    public function __construct(protected Reason $reason, private readonly string $token)
    {
    }
    public function is_valid(): bool
    {
        return false;
    }
    public function is_invalid(): bool
    {
        return true;
    }
    public function description(): string
    {
        return $this->reason->description() . ' in char ' . $this->token;
    }
    public function code(): int
    {
        return $this->reason->code();
    }
    public function reason(): Reason
    {
        return $this->reason;
    }
}