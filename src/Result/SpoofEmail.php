<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result;

use Egulias\Email_Validator\Result\Reason\Spoof_Email as ReasonSpoofEmail;
class Spoof_Email extends Invalid_Email
{
    public function __construct()
    {
        $this->reason = new Reason_Spoof_Email();
        parent::__construct($this->reason, '');
    }
}