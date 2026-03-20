<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Warning;

class Ipv6double_Colon extends Warning
{
    public const CODE = 73;
    public function __construct()
    {
        $this->message = 'Double colon found after IPV6 tag';
        $this->rfc_number = 5322;
    }
}