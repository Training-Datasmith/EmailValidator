<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Warning;

class Ipv6max_Groups extends Warning
{
    public const CODE = 75;
    public function __construct()
    {
        $this->message = 'Reached the maximum number of IPV6 groups allowed';
        $this->rfc_number = 5321;
    }
}