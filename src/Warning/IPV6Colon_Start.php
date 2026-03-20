<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Warning;

class Ipv6colon_Start extends Warning
{
    public const CODE = 76;
    public function __construct()
    {
        $this->message = ':: found at the start of the domain literal';
        $this->rfc_number = 5322;
    }
}