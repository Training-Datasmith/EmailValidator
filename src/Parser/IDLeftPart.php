<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Parser;

use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Result\Reason\Comments_In_Id_Right;
use Egulias\Email_Validator\Result\Result;
class Id_Left_Part extends Local_Part
{
    protected function parse_comments(): Result
    {
        return new Invalid_Email(new Comments_In_Id_Right(), $this->lexer->current->value);
    }
}