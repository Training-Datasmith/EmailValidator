<?php

declare (strict_types=1);
namespace Egulias\Email_Validator;

use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Result\Reason\Expecting_Atext;
use Egulias\Email_Validator\Result\Result;
use Egulias\Email_Validator\Result\Valid_Email;
abstract class Parser
{
    /**
     * @var Warning\Warning[]
     */
    protected $warnings = [];
    /**
     * id-left "@" id-right
     */
    abstract protected function parse_right_from_at(): Result;
    abstract protected function parse_left_from_at(): Result;
    abstract protected function pre_left_parsing(): Result;
    public function __construct(protected \Egulias\Email_Validator\Email_Lexer $lexer)
    {
    }
    public function parse(string $str): Result
    {
        $this->lexer->set_input($str);
        if ($this->lexer->has_invalid_tokens()) {
            return new Invalid_Email(new Expecting_Atext('Invalid tokens found'), $this->lexer->current->value);
        }
        $pre_parsing_result = $this->pre_left_parsing();
        if ($pre_parsing_result->is_invalid()) {
            return $pre_parsing_result;
        }
        $local_part_result = $this->parse_left_from_at();
        if ($local_part_result->is_invalid()) {
            return $local_part_result;
        }
        $domain_part_result = $this->parse_right_from_at();
        if ($domain_part_result->is_invalid()) {
            return $domain_part_result;
        }
        return new Valid_Email();
    }
    /**
     * @return Warning\Warning[]
     */
    public function get_warnings(): array
    {
        return $this->warnings;
    }
    protected function has_at_token(): bool
    {
        $this->lexer->move_next();
        $this->lexer->move_next();
        return !$this->lexer->current->is_a(Email_Lexer::S_AT);
    }
}