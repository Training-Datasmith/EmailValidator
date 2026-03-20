<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Validation;

use Egulias\Email_Validator\Email_Lexer;
use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Warning\Warning;
interface Email_Validation
{
    /**
     * Returns true if the given email is valid.
     *
     * @param string     $email      The email you want to validate.
     * @param EmailLexer $emailLexer The email lexer.
     */
    public function is_valid(string $email, Email_Lexer $email_lexer): bool;
    /**
     * Returns the validation error.
     */
    public function get_error(): ?Invalid_Email;
    /**
     * Returns the validation warnings.
     *
     * @return Warning[]
     */
    public function get_warnings(): array;
}