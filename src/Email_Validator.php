<?php

declare (strict_types=1);
namespace Egulias\Email_Validator;

use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Validation\Email_Validation;
class Email_Validator
{
    private readonly \Egulias\Email_Validator\Email_Lexer $lexer;
    /**
     * @var Warning\Warning[]
     */
    private array $warnings = [];
    private ?\Egulias\Email_Validator\Result\Invalid_Email $error = null;
    public function __construct()
    {
        $this->lexer = new Email_Lexer();
    }
    /**
     * @return bool
     */
    public function is_valid(string $email, Email_Validation $email_validation)
    {
        $is_valid = $email_validation->is_valid($email, $this->lexer);
        $this->warnings = $email_validation->get_warnings();
        $this->error = $email_validation->get_error();
        return $is_valid;
    }
    public function has_warnings(): bool
    {
        return !empty($this->warnings);
    }
    /**
     * @return array
     */
    public function get_warnings()
    {
        return $this->warnings;
    }
    /**
     * @return InvalidEmail|null
     */
    public function get_error()
    {
        return $this->error;
    }
}