<?php

declare(strict_types=1);

namespace Egulias\EmailValidator\Result\Reason;

abstract class DetailedReason implements Reason
{
    public function __construct(protected string $detailedDescription)
    {
    }
}
