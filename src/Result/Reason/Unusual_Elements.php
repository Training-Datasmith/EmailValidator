<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Unusual_Elements implements Reason
{
    public function __construct(private readonly string $element)
    {
    }
    public function code(): int
    {
        return 201;
    }
    public function description(): string
    {
        return 'Unusual element found, wourld render invalid in majority of cases. Element found: ' . $this->element;
    }
}