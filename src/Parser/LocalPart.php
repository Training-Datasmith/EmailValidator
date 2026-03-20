<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Parser;

use Egulias\Email_Validator\Email_Lexer;
use Egulias\Email_Validator\Parser\Comment_Strategy\Local_Comment;
use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Result\Reason\Consecutive_Dot;
use Egulias\Email_Validator\Result\Reason\Dot_At_End;
use Egulias\Email_Validator\Result\Reason\Dot_At_Start;
use Egulias\Email_Validator\Result\Reason\Expecting_Atext;
use Egulias\Email_Validator\Result\Result;
use Egulias\Email_Validator\Result\Valid_Email;
use Egulias\Email_Validator\Warning\Local_Too_Long;
class Local_Part extends Part_Parser
{
    public const INVALID_TOKENS = [Email_Lexer::S_COMMA => Email_Lexer::S_COMMA, Email_Lexer::S_CLOSEBRACKET => Email_Lexer::S_CLOSEBRACKET, Email_Lexer::S_OPENBRACKET => Email_Lexer::S_OPENBRACKET, Email_Lexer::S_GREATERTHAN => Email_Lexer::S_GREATERTHAN, Email_Lexer::S_LOWERTHAN => Email_Lexer::S_LOWERTHAN, Email_Lexer::S_COLON => Email_Lexer::S_COLON, Email_Lexer::S_SEMICOLON => Email_Lexer::S_SEMICOLON, Email_Lexer::INVALID => Email_Lexer::INVALID];
    private string $local_part = '';
    public function parse(): Result
    {
        $this->lexer->clear_recorded();
        $this->lexer->start_recording();
        while (!$this->lexer->current->is_a(Email_Lexer::S_AT) && !$this->lexer->current->is_a(Email_Lexer::S_EMPTY)) {
            if ($this->has_dot_at_start()) {
                return new Invalid_Email(new Dot_At_Start(), $this->lexer->current->value);
            }
            if ($this->lexer->current->is_a(Email_Lexer::S_DQUOTE)) {
                $dquote_parsing_result = $this->parse_double_quote();
                //Invalid double quote parsing
                if ($dquote_parsing_result->is_invalid()) {
                    return $dquote_parsing_result;
                }
            }
            if ($this->lexer->current->is_a(Email_Lexer::S_OPENPARENTHESIS) || $this->lexer->current->is_a(Email_Lexer::S_CLOSEPARENTHESIS)) {
                $comments_result = $this->parse_comments();
                //Invalid comment parsing
                if ($comments_result->is_invalid()) {
                    return $comments_result;
                }
            }
            if ($this->lexer->current->is_a(Email_Lexer::S_DOT) && $this->lexer->is_next_token(Email_Lexer::S_DOT)) {
                return new Invalid_Email(new Consecutive_Dot(), $this->lexer->current->value);
            }
            if ($this->lexer->current->is_a(Email_Lexer::S_DOT) && $this->lexer->is_next_token(Email_Lexer::S_AT)) {
                return new Invalid_Email(new Dot_At_End(), $this->lexer->current->value);
            }
            $result_escaping = $this->validate_escaping();
            if ($result_escaping->is_invalid()) {
                return $result_escaping;
            }
            $result_token = $this->validate_tokens(false);
            if ($result_token->is_invalid()) {
                return $result_token;
            }
            $result_fws = $this->parse_local_fws();
            if ($result_fws->is_invalid()) {
                return $result_fws;
            }
            $this->lexer->move_next();
        }
        $this->lexer->stop_recording();
        $this->local_part = rtrim($this->lexer->get_accumulated_values(), '@');
        if (strlen($this->local_part) > Local_Too_Long::LOCAL_PART_LENGTH) {
            $this->warnings[Local_Too_Long::CODE] = new Local_Too_Long();
        }
        return new Valid_Email();
    }
    protected function validate_tokens(bool $has_comments): Result
    {
        if (isset(self::INVALID_TOKENS[$this->lexer->current->type])) {
            return new Invalid_Email(new Expecting_Atext('Invalid token found'), $this->lexer->current->value);
        }
        return new Valid_Email();
    }
    public function local_part(): string
    {
        return $this->local_part;
    }
    private function parse_local_fws(): Result
    {
        $folding_ws = new Folding_White_Space($this->lexer);
        $result_fws = $folding_ws->parse();
        if ($result_fws->is_valid()) {
            $this->warnings = [...$this->warnings, ...$folding_ws->get_warnings()];
        }
        return $result_fws;
    }
    private function has_dot_at_start(): bool
    {
        return $this->lexer->current->is_a(Email_Lexer::S_DOT) && $this->lexer->get_previous()->is_a(Email_Lexer::S_EMPTY);
    }
    private function parse_double_quote(): Result
    {
        $dquote_parser = new Double_Quote($this->lexer);
        $parse_again = $dquote_parser->parse();
        $this->warnings = [...$this->warnings, ...$dquote_parser->get_warnings()];
        return $parse_again;
    }
    protected function parse_comments(): Result
    {
        $comment_parser = new Comment($this->lexer, new Local_Comment());
        $result = $comment_parser->parse();
        $this->warnings = [...$this->warnings, ...$comment_parser->get_warnings()];
        return $result;
    }
    private function validate_escaping(): Result
    {
        //Backslash found
        if (!$this->lexer->current->is_a(Email_Lexer::S_BACKSLASH)) {
            return new Valid_Email();
        }
        if ($this->lexer->is_next_token(Email_Lexer::GENERIC)) {
            return new Invalid_Email(new Expecting_Atext('Found ATOM after escaping'), $this->lexer->current->value);
        }
        return new Valid_Email();
    }
}