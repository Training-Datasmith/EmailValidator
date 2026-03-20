<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Warning;

class Ipv6colon_End extends Warning
{
    public const CODE = 77;
    public function __construct()
    {
        $this->message = ':: found at the end of the domain literal';
        $this->rfc_number = 5322;
    }
}