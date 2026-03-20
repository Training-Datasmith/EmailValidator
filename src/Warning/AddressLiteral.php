<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Warning;

class Address_Literal extends Warning
{
    public const CODE = 12;
    public function __construct()
    {
        $this->message = 'Address literal in domain part';
        $this->rfc_number = 5321;
    }
}