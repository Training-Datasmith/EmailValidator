<?php

declare(strict_types=1);

namespace Egulias\EmailValidator\Result;

use Egulias\EmailValidator\Result\Reason\Reason;

class InvalidEmail implements Result
{
    public function __construct(protected Reason $reason, private readonly string $token)
    {
    }

    public function isValid(): bool
    {
        return false;
    }

    public function isInvalid(): bool
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
