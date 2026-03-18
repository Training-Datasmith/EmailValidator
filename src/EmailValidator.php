<?php

namespace Egulias\EmailValidator;

use Egulias\EmailValidator\Result\InvalidEmail;
use Egulias\EmailValidator\Validation\EmailValidation;

class EmailValidator
{
    private readonly \Egulias\EmailValidator\EmailLexer $lexer;

    /**
     * @var Warning\Warning[]
     */
    private array $warnings = [];

    private ?\Egulias\EmailValidator\Result\InvalidEmail $error = null;

    public function __construct()
    {
        $this->lexer = new EmailLexer();
    }

    /**
     * @return bool
     */
    public function isValid(string $email, EmailValidation $emailValidation)
    {
        $isValid = $emailValidation->isValid($email, $this->lexer);
        $this->warnings = $emailValidation->getWarnings();
        $this->error = $emailValidation->getError();

        return $isValid;
    }

    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    /**
     * @return array
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    /**
     * @return InvalidEmail|null
     */
    public function getError()
    {
        return $this->error;
    }
}
