<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Warning;

class Domain_Literal extends Warning
{
    public const CODE = 70;
    public function __construct()
    {
        $this->message = 'Domain Literal';
        $this->rfc_number = 5322;
    }
}