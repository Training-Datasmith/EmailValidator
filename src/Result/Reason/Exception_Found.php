<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Exception_Found implements Reason
{
    public function __construct(private readonly \Exception $exception)
    {
    }
    public function code(): int
    {
        return 999;
    }
    public function description(): string
    {
        return $this->exception->get_message();
    }
}