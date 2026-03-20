<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Warning;

use Egulias\Email_Validator\Email_Parser;
class Email_Too_Long extends Warning
{
    public const CODE = 66;
    public function __construct()
    {
        $this->message = 'Email is too long, exceeds ' . Email_Parser::EMAIL_MAX_LENGTH;
    }
}