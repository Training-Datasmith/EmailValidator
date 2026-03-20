<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Parser;

use Egulias\Email_Validator\Email_Lexer;
use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Result\Reason\Expecting_Atext;
use Egulias\Email_Validator\Result\Result;
use Egulias\Email_Validator\Result\Valid_Email;
class Id_Right_Part extends Domain_Part
{
    protected function validate_tokens(bool $has_comments): Result
    {
        $invalid_domain_tokens = [Email_Lexer::S_DQUOTE => true, Email_Lexer::S_SQUOTE => true, Email_Lexer::S_BACKTICK => true, Email_Lexer::S_SEMICOLON => true, Email_Lexer::S_GREATERTHAN => true, Email_Lexer::S_LOWERTHAN => true];
        if (isset($invalid_domain_tokens[$this->lexer->current->type])) {
            return new Invalid_Email(new Expecting_Atext('Invalid token in domain: ' . $this->lexer->current->value), $this->lexer->current->value);
        }
        return new Valid_Email();
    }
}