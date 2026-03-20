<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Validation;

use Egulias\Email_Validator\Email_Lexer;
use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Result\Reason\Rfc_Warnings;
class No_Rfc_Warnings_Validation extends Rfc_Validation
{
    private ?\Egulias\Email_Validator\Result\Invalid_Email $error = null;
    /**
     * {@inheritdoc}
     */
    public function is_valid(string $email, Email_Lexer $email_lexer): bool
    {
        if (!parent::is_valid($email, $email_lexer)) {
            return false;
        }
        if (empty($this->get_warnings())) {
            return true;
        }
        $this->error = new Invalid_Email(new Rfc_Warnings(), '');
        return false;
    }
    /**
     * {@inheritdoc}
     */
    public function get_error(): ?Invalid_Email
    {
        return $this->error ?: parent::get_error();
    }
}