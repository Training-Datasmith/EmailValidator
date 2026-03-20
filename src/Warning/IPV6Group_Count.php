<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Warning;

class Ipv6group_Count extends Warning
{
    public const CODE = 72;
    public function __construct()
    {
        $this->message = 'Group count is not IPV6 valid';
        $this->rfc_number = 5322;
    }
}