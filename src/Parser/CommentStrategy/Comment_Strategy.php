<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Parser\Comment_Strategy;

use Egulias\Email_Validator\Email_Lexer;
use Egulias\Email_Validator\Result\Result;
use Egulias\Email_Validator\Warning\Warning;
interface Comment_Strategy
{
    /**
     * Return "true" to continue, "false" to exit
     */
    public function exit_condition(Email_Lexer $lexer, int $opened_parenthesis): bool;
    public function end_of_loop_validations(Email_Lexer $lexer): Result;
    /**
     * @return Warning[]
     */
    public function get_warnings(): array;
}