<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Parser;

use Doctrine\Common\Lexer\Token;
use Egulias\Email_Validator\Email_Lexer;
use Egulias\Email_Validator\Parser\Comment_Strategy\Domain_Comment;
use Egulias\Email_Validator\Parser\Domain_Literal as DomainLiteralParser;
use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Result\Reason\Char_Not_Allowed;
use Egulias\Email_Validator\Result\Reason\Consecutive_At;
use Egulias\Email_Validator\Result\Reason\Crlf_At_The_End;
use Egulias\Email_Validator\Result\Reason\Domain_Hyphened;
use Egulias\Email_Validator\Result\Reason\Domain_Too_Long;
use Egulias\Email_Validator\Result\Reason\Dot_At_End;
use Egulias\Email_Validator\Result\Reason\Dot_At_Start;
use Egulias\Email_Validator\Result\Reason\Expecting_Atext;
use Egulias\Email_Validator\Result\Reason\Expecting_Domain_Literal_Close;
use Egulias\Email_Validator\Result\Reason\Label_Too_Long;
use Egulias\Email_Validator\Result\Reason\No_Domain_Part;
use Egulias\Email_Validator\Result\Result;
use Egulias\Email_Validator\Result\Valid_Email;
use Egulias\Email_Validator\Warning\Deprecated_Comment;
use Egulias\Email_Validator\Warning\TLD;
class Domain_Part extends Part_Parser
{
    public const DOMAIN_MAX_LENGTH = 253;
    public const LABEL_MAX_LENGTH = 63;
    /**
     * @var string
     */
    protected $domain_part = '';
    /**
     * @var string
     */
    protected $label = '';
    public function parse(): Result
    {
        $this->lexer->clear_recorded();
        $this->lexer->start_recording();
        $this->lexer->move_next();
        $domain_checks = $this->perform_domain_start_checks();
        if ($domain_checks->is_invalid()) {
            return $domain_checks;
        }
        if ($this->lexer->current->is_a(Email_Lexer::S_AT)) {
            return new Invalid_Email(new Consecutive_At(), $this->lexer->current->value);
        }
        $result = $this->do_parse_domain_part();
        if ($result->is_invalid()) {
            return $result;
        }
        $end = $this->check_end_of_domain();
        if ($end->is_invalid()) {
            return $end;
        }
        $this->lexer->stop_recording();
        $this->domain_part = $this->lexer->get_accumulated_values();
        $length = strlen($this->domain_part);
        if ($length > self::DOMAIN_MAX_LENGTH) {
            return new Invalid_Email(new Domain_Too_Long(), $this->lexer->current->value);
        }
        return new Valid_Email();
    }
    private function check_end_of_domain(): Result
    {
        $prev = $this->lexer->get_previous();
        if ($prev->is_a(Email_Lexer::S_DOT)) {
            return new Invalid_Email(new Dot_At_End(), $this->lexer->current->value);
        }
        if ($prev->is_a(Email_Lexer::S_HYPHEN)) {
            return new Invalid_Email(new Domain_Hyphened('Hypen found at the end of the domain'), $prev->value);
        }
        if ($this->lexer->current->is_a(Email_Lexer::S_SP)) {
            return new Invalid_Email(new Crlf_At_The_End(), $prev->value);
        }
        return new Valid_Email();
    }
    private function perform_domain_start_checks(): Result
    {
        $invalid_tokens = $this->check_invalid_tokens_after_at();
        if ($invalid_tokens->is_invalid()) {
            return $invalid_tokens;
        }
        $missing_domain = $this->check_empty_domain();
        if ($missing_domain->is_invalid()) {
            return $missing_domain;
        }
        if ($this->lexer->current->is_a(Email_Lexer::S_OPENPARENTHESIS)) {
            $this->warnings[Deprecated_Comment::CODE] = new Deprecated_Comment();
        }
        return new Valid_Email();
    }
    private function check_empty_domain(): Result
    {
        $there_is_no_domain = $this->lexer->current->is_a(Email_Lexer::S_EMPTY) || $this->lexer->current->is_a(Email_Lexer::S_SP) && !$this->lexer->is_next_token(Email_Lexer::GENERIC);
        if ($there_is_no_domain) {
            return new Invalid_Email(new No_Domain_Part(), $this->lexer->current->value);
        }
        return new Valid_Email();
    }
    private function check_invalid_tokens_after_at(): Result
    {
        if ($this->lexer->current->is_a(Email_Lexer::S_DOT)) {
            return new Invalid_Email(new Dot_At_Start(), $this->lexer->current->value);
        }
        if ($this->lexer->current->is_a(Email_Lexer::S_HYPHEN)) {
            return new Invalid_Email(new Domain_Hyphened('After AT'), $this->lexer->current->value);
        }
        return new Valid_Email();
    }
    protected function parse_comments(): Result
    {
        $comment_parser = new Comment($this->lexer, new Domain_Comment());
        $result = $comment_parser->parse();
        $this->warnings = [...$this->warnings, ...$comment_parser->get_warnings()];
        return $result;
    }
    protected function do_parse_domain_part(): Result
    {
        $tld_missing = true;
        $has_comments = false;
        $domain = '';
        do {
            $prev = $this->lexer->get_previous();
            $not_allowed_chars = $this->check_not_allowed_chars($this->lexer->current);
            if ($not_allowed_chars->is_invalid()) {
                return $not_allowed_chars;
            }
            if ($this->lexer->current->is_a(Email_Lexer::S_OPENPARENTHESIS) || $this->lexer->current->is_a(Email_Lexer::S_CLOSEPARENTHESIS)) {
                $has_comments = true;
                $comments_result = $this->parse_comments();
                //Invalid comment parsing
                if ($comments_result->is_invalid()) {
                    return $comments_result;
                }
            }
            $dots_result = $this->check_consecutive_dots();
            if ($dots_result->is_invalid()) {
                return $dots_result;
            }
            if ($this->lexer->current->is_a(Email_Lexer::S_OPENBRACKET)) {
                $literal_result = $this->parse_domain_literal();
                $this->add_tld_warnings($tld_missing);
                return $literal_result;
            }
            $label_check = $this->check_label_length();
            if ($label_check->is_invalid()) {
                return $label_check;
            }
            $fws_result = $this->parse_fws();
            if ($fws_result->is_invalid()) {
                return $fws_result;
            }
            $domain .= $this->lexer->current->value;
            if ($this->lexer->current->is_a(Email_Lexer::S_DOT) && $this->lexer->is_next_token(Email_Lexer::GENERIC)) {
                $tld_missing = false;
            }
            $exceptions_result = $this->check_domain_part_exceptions($prev, $has_comments);
            if ($exceptions_result->is_invalid()) {
                return $exceptions_result;
            }
            $this->lexer->move_next();
        } while (!$this->lexer->current->is_a(Email_Lexer::S_EMPTY));
        $label_check = $this->check_label_length(true);
        if ($label_check->is_invalid()) {
            return $label_check;
        }
        $this->add_tld_warnings($tld_missing);
        $this->domain_part = $domain;
        return new Valid_Email();
    }
    /**
     * @param Token<int, string> $token
     */
    private function check_not_allowed_chars(Token $token): Result
    {
        $not_allowed = [Email_Lexer::S_BACKSLASH => true, Email_Lexer::S_SLASH => true];
        if (isset($not_allowed[$token->type])) {
            return new Invalid_Email(new Char_Not_Allowed(), $token->value);
        }
        return new Valid_Email();
    }
    protected function parse_domain_literal(): Result
    {
        try {
            $this->lexer->find(Email_Lexer::S_CLOSEBRACKET);
        } catch (\RuntimeException) {
            return new Invalid_Email(new Expecting_Domain_Literal_Close(), $this->lexer->current->value);
        }
        $domain_literal_parser = new Domain_Literal_Parser($this->lexer);
        $result = $domain_literal_parser->parse();
        $this->warnings = [...$this->warnings, ...$domain_literal_parser->get_warnings()];
        return $result;
    }
    /**
     * @param Token<int, string> $prev
     *
     */
    protected function check_domain_part_exceptions(Token $prev, bool $has_comments): Result
    {
        if ($this->lexer->current->is_a(Email_Lexer::S_OPENBRACKET) && $prev->type !== Email_Lexer::S_AT) {
            return new Invalid_Email(new Expecting_Atext('OPENBRACKET not after AT'), $this->lexer->current->value);
        }
        if ($this->lexer->current->is_a(Email_Lexer::S_HYPHEN) && $this->lexer->is_next_token(Email_Lexer::S_DOT)) {
            return new Invalid_Email(new Domain_Hyphened('Hypen found near DOT'), $this->lexer->current->value);
        }
        if ($this->lexer->current->is_a(Email_Lexer::S_BACKSLASH) && $this->lexer->is_next_token(Email_Lexer::GENERIC)) {
            return new Invalid_Email(new Expecting_Atext('Escaping following "ATOM"'), $this->lexer->current->value);
        }
        return $this->validate_tokens($has_comments);
    }
    protected function validate_tokens(bool $has_comments): Result
    {
        $valid_domain_tokens = [Email_Lexer::GENERIC => true, Email_Lexer::S_HYPHEN => true, Email_Lexer::S_DOT => true];
        if ($has_comments) {
            $valid_domain_tokens[Email_Lexer::S_OPENPARENTHESIS] = true;
            $valid_domain_tokens[Email_Lexer::S_CLOSEPARENTHESIS] = true;
        }
        if (!isset($valid_domain_tokens[$this->lexer->current->type])) {
            return new Invalid_Email(new Expecting_Atext('Invalid token in domain: ' . $this->lexer->current->value), $this->lexer->current->value);
        }
        return new Valid_Email();
    }
    private function check_label_length(bool $is_end_of_domain = false): Result
    {
        if ($this->lexer->current->is_a(Email_Lexer::S_DOT) || $is_end_of_domain) {
            if ($this->is_label_too_long($this->label)) {
                return new Invalid_Email(new Label_Too_Long(), $this->lexer->current->value);
            }
            $this->label = '';
        }
        $this->label .= $this->lexer->current->value;
        return new Valid_Email();
    }
    private function is_label_too_long(string $label): bool
    {
        if (preg_match('/[^\x00-\x7F]/', $label)) {
            idn_to_ascii($label, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46, $idna_info);
            /** @psalm-var array{errors: int, ...} $idnaInfo */
            return (bool) ($idna_info['errors'] & IDNA_ERROR_LABEL_TOO_LONG);
        }
        return strlen($label) > self::LABEL_MAX_LENGTH;
    }
    private function add_tld_warnings(bool $is_tld_missing): void
    {
        if ($is_tld_missing) {
            $this->warnings[TLD::CODE] = new TLD();
        }
    }
    public function domain_part(): string
    {
        return $this->domain_part;
    }
}