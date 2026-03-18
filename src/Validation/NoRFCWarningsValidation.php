<?php

declare(strict_types=1);

namespace Egulias\EmailValidator\Validation;

use Egulias\EmailValidator\EmailLexer;
use Egulias\EmailValidator\Result\InvalidEmail;
use Egulias\EmailValidator\Result\Reason\RFCWarnings;

class NoRFCWarningsValidation extends RFCValidation
{
    private ?\Egulias\EmailValidator\Result\InvalidEmail $error = null;

    /**
     * {@inheritdoc}
     */
    public function isValid(string $email, EmailLexer $emailLexer): bool
    {
        if (!parent::isValid($email, $emailLexer)) {
            return false;
        }

        if (empty($this->getWarnings())) {
            return true;
        }

        $this->error = new InvalidEmail(new RFCWarnings(), '');

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getError(): ?InvalidEmail
    {
        return $this->error ?: parent::getError();
    }
}
