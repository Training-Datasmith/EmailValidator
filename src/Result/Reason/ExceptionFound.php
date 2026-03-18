<?php

namespace Egulias\EmailValidator\Result\Reason;

class ExceptionFound implements Reason
{
    public function __construct(private readonly \Exception $exception)
    {
    }
    public function code() : int
    {
        return 999;
    }

    public function description() : string
    {
        return $this->exception->getMessage();
    }
}
