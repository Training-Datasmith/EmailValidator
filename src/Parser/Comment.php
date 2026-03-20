<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Parser;

use Egulias\Email_Validator\Email_Lexer;
use Egulias\Email_Validator\Parser\Comment_Strategy\Comment_Strategy;
use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Result\Reason\Unclosed_Comment;
use Egulias\Email_Validator\Result\Reason\Un_Opened_Comment;
use Egulias\Email_Validator\Result\Result;
use Egulias\Email_Validator\Warning\Comment as WarningComment;
use Egulias\Email_Validator\Warning\Quoted_Part;
class Comment extends Part_Parser
{
    private int $opened_parenthesis = 0;
    public function __construct(Email_Lexer $lexer, private readonly Comment_Strategy $comment_strategy)
    {
        $this->lexer = $lexer;
    }
    public function parse(): Result
    {
        if ($this->lexer->current->is_a(Email_Lexer::S_OPENPARENTHESIS)) {
            $this->opened_parenthesis++;
            if ($this->no_closing_parenthesis()) {
                return new Invalid_Email(new Unclosed_Comment(), $this->lexer->current->value);
            }
        }
        if ($this->lexer->current->is_a(Email_Lexer::S_CLOSEPARENTHESIS)) {
            return new Invalid_Email(new Un_Opened_Comment(), $this->lexer->current->value);
        }
        $this->warnings[Warning_Comment::CODE] = new Warning_Comment();
        $more_tokens = true;
        while ($this->comment_strategy->exit_condition($this->lexer, $this->opened_parenthesis) && $more_tokens) {
            if ($this->lexer->is_next_token(Email_Lexer::S_OPENPARENTHESIS)) {
                $this->opened_parenthesis++;
            }
            $this->warn_escaping();
            if ($this->lexer->is_next_token(Email_Lexer::S_CLOSEPARENTHESIS)) {
                $this->opened_parenthesis--;
            }
            $more_tokens = $this->lexer->move_next();
        }
        if ($this->opened_parenthesis >= 1) {
            return new Invalid_Email(new Unclosed_Comment(), $this->lexer->current->value);
        }
        if ($this->opened_parenthesis < 0) {
            return new Invalid_Email(new Un_Opened_Comment(), $this->lexer->current->value);
        }
        $final_validations = $this->comment_strategy->end_of_loop_validations($this->lexer);
        $this->warnings = [...$this->warnings, ...$this->comment_strategy->get_warnings()];
        return $final_validations;
    }
    private function warn_escaping(): void
    {
        //Backslash found
        if (!$this->lexer->current->is_a(Email_Lexer::S_BACKSLASH)) {
            return;
        }
        if (!$this->lexer->is_next_token_any([Email_Lexer::S_SP, Email_Lexer::S_HTAB, Email_Lexer::C_DEL])) {
            return;
        }
        $this->warnings[Quoted_Part::CODE] = new Quoted_Part($this->lexer->get_previous()->type, $this->lexer->current->type);
    }
    private function no_closing_parenthesis(): bool
    {
        try {
            $this->lexer->find(Email_Lexer::S_CLOSEPARENTHESIS);
            return false;
        } catch (\RuntimeException) {
            return true;
        }
    }
}