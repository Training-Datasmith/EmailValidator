<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Warning;

class TLD extends Warning
{
    public const CODE = 9;
    public function __construct()
    {
        $this->message = 'RFC5321, TLD';
    }
}