<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Warning;

class Cfws_Near_At extends Warning
{
    public const CODE = 49;
    public function __construct()
    {
        $this->message = 'Deprecated folding white space near @';
    }
}