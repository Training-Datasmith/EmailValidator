<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Parser\Comment_Strategy;

use Egulias\Email_Validator\Email_Lexer;
use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Result\Reason\Expecting_Atext;
use Egulias\Email_Validator\Result\Result;
use Egulias\Email_Validator\Result\Valid_Email;
class Domain_Comment implements Comment_Strategy
{
    public function exit_condition(Email_Lexer $lexer, int $opened_parenthesis): bool
    {
        return !($opened_parenthesis === 0 && $lexer->is_next_token(Email_Lexer::S_DOT));
    }
    public function end_of_loop_validations(Email_Lexer $lexer): Result
    {
        //test for end of string
        if (!$lexer->is_next_token(Email_Lexer::S_DOT)) {
            return new Invalid_Email(new Expecting_Atext('DOT not found near CLOSEPARENTHESIS'), $lexer->current->value);
        }
        //add warning
        //Address is valid within the message but cannot be used unmodified for the envelope
        return new Valid_Email();
    }
    public function get_warnings(): array
    {
        return [];
    }
}