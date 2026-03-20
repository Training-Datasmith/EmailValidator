<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Parser;

use Egulias\Email_Validator\Email_Lexer;
use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Result\Reason\Atext_After_Cfws;
use Egulias\Email_Validator\Result\Reason\Crlf_At_The_End;
use Egulias\Email_Validator\Result\Reason\CRLFX2;
use Egulias\Email_Validator\Result\Reason\Cr_No_Lf;
use Egulias\Email_Validator\Result\Reason\Expecting_Ctext;
use Egulias\Email_Validator\Result\Result;
use Egulias\Email_Validator\Result\Valid_Email;
use Egulias\Email_Validator\Warning\Cfws_Near_At;
use Egulias\Email_Validator\Warning\Cfws_With_Fws;
class Folding_White_Space extends Part_Parser
{
    public const FWS_TYPES = [Email_Lexer::S_SP, Email_Lexer::S_HTAB, Email_Lexer::S_CR, Email_Lexer::S_LF, Email_Lexer::CRLF];
    public function parse(): Result
    {
        if (!$this->is_fws()) {
            return new Valid_Email();
        }
        $previous = $this->lexer->get_previous();
        $result_crlf = $this->check_crlf_in_fws();
        if ($result_crlf->is_invalid()) {
            return $result_crlf;
        }
        if ($this->lexer->current->is_a(Email_Lexer::S_CR)) {
            return new Invalid_Email(new Cr_No_Lf(), $this->lexer->current->value);
        }
        if ($this->lexer->is_next_token(Email_Lexer::GENERIC) && !$previous->is_a(Email_Lexer::S_AT)) {
            return new Invalid_Email(new Atext_After_Cfws(), $this->lexer->current->value);
        }
        if ($this->lexer->current->is_a(Email_Lexer::S_LF) || $this->lexer->current->is_a(Email_Lexer::C_NUL)) {
            return new Invalid_Email(new Expecting_Ctext(), $this->lexer->current->value);
        }
        if ($this->lexer->is_next_token(Email_Lexer::S_AT) || $previous->is_a(Email_Lexer::S_AT)) {
            $this->warnings[Cfws_Near_At::CODE] = new Cfws_Near_At();
        } else {
            $this->warnings[Cfws_With_Fws::CODE] = new Cfws_With_Fws();
        }
        return new Valid_Email();
    }
    protected function check_crlf_in_fws(): Result
    {
        if (!$this->lexer->current->is_a(Email_Lexer::CRLF)) {
            return new Valid_Email();
        }
        if (!$this->lexer->is_next_token_any([Email_Lexer::S_SP, Email_Lexer::S_HTAB])) {
            return new Invalid_Email(new CRLFX2(), $this->lexer->current->value);
        }
        //this has no coverage. Condition is repeated from above one
        if (!$this->lexer->is_next_token_any([Email_Lexer::S_SP, Email_Lexer::S_HTAB])) {
            return new Invalid_Email(new Crlf_At_The_End(), $this->lexer->current->value);
        }
        return new Valid_Email();
    }
    protected function is_fws(): bool
    {
        if ($this->escaped()) {
            return false;
        }
        return in_array($this->lexer->current->type, self::FWS_TYPES);
    }
}