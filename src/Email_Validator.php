<?php

declare (strict_types=1);
namespace Egulias\Email_Validator;

use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Validation\Email_Validation;
class Email_Validator
{
    /** @var \Egulias\Email_Validator\Email_Lexer Tokeniser reused across validation calls */
    private readonly \Egulias\Email_Validator\Email_Lexer $lexer;

    /**
     * RFC-compliant warnings accumulated during the last {@see is_valid()} call.
     *
     * @var \Egulias\Email_Validator\Warning\Warning[]
     */
    private array $warnings = [];

    /** @var Invalid_Email|null The specific invalidity reason from the last validation, or null if valid */
    private ?\Egulias\Email_Validator\Result\Invalid_Email $error = null;

    /**
     * Creates a new validator instance with a fresh lexer.
     */
    public function __construct()
    {
        $this->lexer = new Email_Lexer();
    }

    /**
     * Validates an email address against the supplied validation strategy.
     *
     * The validator delegates all logic to the `$email_validation` object, which
     * may perform RFC syntax checks, DNS MX lookups, spoof detection, etc.
     * After the call, use {@see get_warnings()} and {@see get_error()} to
     * retrieve details about non-fatal issues or the specific failure reason.
     *
     * @param string          $email            The email address string to validate.
     * @param Email_Validation $email_validation The validation strategy to apply
     *                                           (e.g. RFC_Validation, DNS_Check_Validation).
     *
     * @return bool `true` if the email is considered valid by the strategy, `false` otherwise.
     */
    public function is_valid(string $email, Email_Validation $email_validation): bool
    {
        $is_valid = $email_validation->is_valid($email, $this->lexer);
        $this->warnings = $email_validation->get_warnings();
        $this->error = $email_validation->get_error();
        return $is_valid;
    }

    /**
     * Returns whether the last validation produced any RFC warnings.
     *
     * Warnings indicate constructs that are technically valid but deprecated or
     * unusual (e.g. quoted local parts, comments, folding whitespace).
     *
     * @return bool `true` if one or more warnings were emitted.
     */
    public function has_warnings(): bool
    {
        return !empty($this->warnings);
    }

    /**
     * Returns the list of RFC warnings from the last validation call.
     *
     * @return \Egulias\Email_Validator\Warning\Warning[] Ordered list of warning objects; empty if none.
     */
    public function get_warnings(): array
    {
        return $this->warnings;
    }

    /**
     * Returns the invalidity reason from the last failed validation, or null if the address was valid.
     *
     * @return Invalid_Email|null The specific failure reason, or `null` if validation passed.
     */
    public function get_error(): ?Invalid_Email
    {
        return $this->error;
    }
}