<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Warning;

class No_Dnsmx_Record extends Warning
{
    public const CODE = 6;
    public function __construct()
    {
        $this->message = 'No MX DSN record was found for this email';
        $this->rfc_number = 5321;
    }
}