<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Warning;

class Cfws_With_Fws extends Warning
{
    public const CODE = 18;
    public function __construct()
    {
        $this->message = 'Folding whites space followed by folding white space';
    }
}