<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Warning;

class Deprecated_Comment extends Warning
{
    public const CODE = 37;
    public function __construct()
    {
        $this->message = 'Deprecated comments';
    }
}