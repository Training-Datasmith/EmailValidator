<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Parser;

use Egulias\Email_Validator\Email_Lexer;
use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Result\Reason\Consecutive_Dot;
use Egulias\Email_Validator\Result\Result;
use Egulias\Email_Validator\Result\Valid_Email;
use Egulias\Email_Validator\Warning\Warning;
abstract class Part_Parser
{
    /**
     * @var Warning[]
     */
    protected $warnings = [];
    public function __construct(protected \Egulias\Email_Validator\Email_Lexer $lexer)
    {
    }
    abstract public function parse(): Result;
    /**
     * @return Warning[]
     */
    public function get_warnings()
    {
        return $this->warnings;
    }
    protected function parse_fws(): Result
    {
        $folding_ws = new Folding_White_Space($this->lexer);
        $result_fws = $folding_ws->parse();
        $this->warnings = [...$this->warnings, ...$folding_ws->get_warnings()];
        return $result_fws;
    }
    protected function check_consecutive_dots(): Result
    {
        if ($this->lexer->current->is_a(Email_Lexer::S_DOT) && $this->lexer->is_next_token(Email_Lexer::S_DOT)) {
            return new Invalid_Email(new Consecutive_Dot(), $this->lexer->current->value);
        }
        return new Valid_Email();
    }
    protected function escaped(): bool
    {
        $previous = $this->lexer->get_previous();
        return $previous->is_a(Email_Lexer::S_BACKSLASH) && !$this->lexer->current->is_a(Email_Lexer::GENERIC);
    }
}