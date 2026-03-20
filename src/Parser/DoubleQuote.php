<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Parser;

use Egulias\Email_Validator\Email_Lexer;
use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Result\Reason\Expecting_Atext;
use Egulias\Email_Validator\Result\Reason\Unclosed_Quoted_String;
use Egulias\Email_Validator\Result\Result;
use Egulias\Email_Validator\Result\Valid_Email;
use Egulias\Email_Validator\Warning\Cfws_With_Fws;
use Egulias\Email_Validator\Warning\Quoted_String;
class Double_Quote extends Part_Parser
{
    public function parse(): Result
    {
        $valid_quoted_string = $this->check_dquote();
        if ($valid_quoted_string->is_invalid()) {
            return $valid_quoted_string;
        }
        $special = [Email_Lexer::S_CR => true, Email_Lexer::S_HTAB => true, Email_Lexer::S_LF => true];
        $invalid = [Email_Lexer::C_NUL => true, Email_Lexer::S_HTAB => true, Email_Lexer::S_CR => true, Email_Lexer::S_LF => true];
        $set_specials_warning = true;
        $this->lexer->move_next();
        while (!$this->lexer->current->is_a(Email_Lexer::S_DQUOTE) && !$this->lexer->current->is_a(Email_Lexer::S_EMPTY)) {
            if (isset($special[$this->lexer->current->type]) && $set_specials_warning) {
                $this->warnings[Cfws_With_Fws::CODE] = new Cfws_With_Fws();
                $set_specials_warning = false;
            }
            if ($this->lexer->current->is_a(Email_Lexer::S_BACKSLASH) && $this->lexer->is_next_token(Email_Lexer::S_DQUOTE)) {
                $this->lexer->move_next();
            }
            $this->lexer->move_next();
            if (!$this->escaped() && isset($invalid[$this->lexer->current->type])) {
                return new Invalid_Email(new Expecting_Atext('Expecting ATEXT between DQUOTE'), $this->lexer->current->value);
            }
        }
        $prev = $this->lexer->get_previous();
        if ($prev->is_a(Email_Lexer::S_BACKSLASH)) {
            $valid_quoted_string = $this->check_dquote();
            if ($valid_quoted_string->is_invalid()) {
                return $valid_quoted_string;
            }
        }
        if (!$this->lexer->is_next_token(Email_Lexer::S_AT) && !$prev->is_a(Email_Lexer::S_BACKSLASH)) {
            return new Invalid_Email(new Expecting_Atext('Expecting ATEXT between DQUOTE'), $this->lexer->current->value);
        }
        return new Valid_Email();
    }
    protected function check_dquote(): Result
    {
        $previous = $this->lexer->get_previous();
        if ($this->lexer->is_next_token(Email_Lexer::GENERIC) && $previous->is_a(Email_Lexer::GENERIC)) {
            $description = 'https://tools.ietf.org/html/rfc5322#section-3.2.4 - quoted string should be a unit';
            return new Invalid_Email(new Expecting_Atext($description), $this->lexer->current->value);
        }
        try {
            $this->lexer->find(Email_Lexer::S_DQUOTE);
        } catch (\Exception) {
            return new Invalid_Email(new Unclosed_Quoted_String(), $this->lexer->current->value);
        }
        $this->warnings[Quoted_String::CODE] = new Quoted_String($previous->value, $this->lexer->current->value);
        return new Valid_Email();
    }
}