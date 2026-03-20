<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Warning;

class Obsolete_Dtext extends Warning
{
    public const CODE = 71;
    public function __construct()
    {
        $this->rfc_number = 5322;
        $this->message = 'Obsolete DTEXT in domain literal';
    }
}