<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Warning;

class IPV6Deprecated extends Warning
{
    public const CODE = 13;
    public function __construct()
    {
        $this->message = 'Deprecated form of IPV6';
        $this->rfc_number = 5321;
    }
}