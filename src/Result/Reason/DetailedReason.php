<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

abstract class Detailed_Reason implements Reason
{
    public function __construct(protected string $detailed_description)
    {
    }
}