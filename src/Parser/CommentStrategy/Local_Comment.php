<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Parser\Comment_Strategy;

use Egulias\Email_Validator\Email_Lexer;
use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Result\Reason\Expecting_Atext;
use Egulias\Email_Validator\Result\Result;
use Egulias\Email_Validator\Result\Valid_Email;
use Egulias\Email_Validator\Warning\Cfws_Near_At;
use Egulias\Email_Validator\Warning\Warning;
class Local_Comment implements Comment_Strategy
{
    /**
     * @var array<int, Warning>
     */
    private array $warnings = [];
    public function exit_condition(Email_Lexer $lexer, int $opened_parenthesis): bool
    {
        return !$lexer->is_next_token(Email_Lexer::S_AT);
    }
    public function end_of_loop_validations(Email_Lexer $lexer): Result
    {
        if (!$lexer->is_next_token(Email_Lexer::S_AT)) {
            return new Invalid_Email(new Expecting_Atext('ATEX is not expected after closing comments'), $lexer->current->value);
        }
        $this->warnings[Cfws_Near_At::CODE] = new Cfws_Near_At();
        return new Valid_Email();
    }
    public function get_warnings(): array
    {
        return $this->warnings;
    }
}