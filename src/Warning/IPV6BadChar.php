<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Warning;

class Ipv6bad_Char extends Warning
{
    public const CODE = 74;
    public function __construct()
    {
        $this->message = 'Bad char in IPV6 domain literal';
        $this->rfc_number = 5322;
    }
}