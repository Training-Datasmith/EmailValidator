<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Validation\Extra;

use Egulias\Email_Validator\Email_Lexer;
use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Result\Spoof_Email;
use Egulias\Email_Validator\Validation\Email_Validation;
use Spoofchecker;
class Spoof_Check_Validation implements Email_Validation
{
    /**
     * @var InvalidEmail|null
     */
    private ?\Egulias\Email_Validator\Result\Spoof_Email $error = null;
    public function __construct()
    {
        if (!extension_loaded('intl')) {
            throw new \LogicException(sprintf('The %s class requires the Intl extension.', self::class));
        }
    }
    public function is_valid(string $email, Email_Lexer $email_lexer): bool
    {
        $checker = new Spoofchecker();
        $checker->set_checks(Spoofchecker::SINGLE_SCRIPT);
        if ($checker->is_suspicious($email)) {
            $this->error = new Spoof_Email();
        }
        return $this->error === null;
    }
    public function get_error(): ?Invalid_Email
    {
        return $this->error;
    }
    public function get_warnings(): array
    {
        return [];
    }
}