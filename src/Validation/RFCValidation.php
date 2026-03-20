<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Validation;

use Egulias\Email_Validator\Email_Lexer;
use Egulias\Email_Validator\Email_Parser;
use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Result\Reason\Exception_Found;
use Egulias\Email_Validator\Warning\Warning;
class Rfc_Validation implements Email_Validation
{
    /**
     * @var Warning[]
     */
    private array $warnings = [];
    /**
     * @var ?InvalidEmail
     */
    private \Egulias\Email_Validator\Result\Result|\Egulias\Email_Validator\Result\Invalid_Email|null $error = null;
    public function is_valid(string $email, Email_Lexer $email_lexer): bool
    {
        $parser = new Email_Parser($email_lexer);
        try {
            $result = $parser->parse($email);
            $this->warnings = $parser->get_warnings();
            if ($result->is_invalid()) {
                /** @psalm-suppress PropertyTypeCoercion */
                $this->error = $result;
                return false;
            }
        } catch (\Exception $invalid) {
            $this->error = new Invalid_Email(new Exception_Found($invalid), '');
            return false;
        }
        return true;
    }
    public function get_error(): ?Invalid_Email
    {
        return $this->error;
    }
    /**
     * @return Warning[]
     */
    public function get_warnings(): array
    {
        return $this->warnings;
    }
}