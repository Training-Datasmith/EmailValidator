<?php

declare (strict_types=1);
namespace Egulias\Email_Validator;

use Egulias\Email_Validator\Parser\Domain_Part;
use Egulias\Email_Validator\Parser\Local_Part;
use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Result\Reason\No_Local_Part;
use Egulias\Email_Validator\Result\Result;
use Egulias\Email_Validator\Result\Valid_Email;
use Egulias\Email_Validator\Warning\Email_Too_Long;
class Email_Parser extends Parser
{
    public const EMAIL_MAX_LENGTH = 254;
    /**
     * @var string
     */
    protected $domain_part = '';
    /**
     * @var string
     */
    protected $local_part = '';
    public function parse(string $str): Result
    {
        $result = parent::parse($str);
        $this->add_long_email_warning($this->local_part, $this->domain_part);
        return $result;
    }
    protected function pre_left_parsing(): Result
    {
        if (!$this->has_at_token()) {
            return new Invalid_Email(new No_Local_Part(), $this->lexer->current->value);
        }
        return new Valid_Email();
    }
    protected function parse_left_from_at(): Result
    {
        return $this->process_local_part();
    }
    protected function parse_right_from_at(): Result
    {
        return $this->process_domain_part();
    }
    private function process_local_part(): Result
    {
        $local_part_parser = new Local_Part($this->lexer);
        $local_part_result = $local_part_parser->parse();
        $this->local_part = $local_part_parser->local_part();
        $this->warnings = [...$local_part_parser->get_warnings(), ...$this->warnings];
        return $local_part_result;
    }
    private function process_domain_part(): Result
    {
        $domain_part_parser = new Domain_Part($this->lexer);
        $domain_part_result = $domain_part_parser->parse();
        $this->domain_part = $domain_part_parser->domain_part();
        $this->warnings = [...$domain_part_parser->get_warnings(), ...$this->warnings];
        return $domain_part_result;
    }
    public function get_domain_part(): string
    {
        return $this->domain_part;
    }
    public function get_local_part(): string
    {
        return $this->local_part;
    }
    private function add_long_email_warning(string $local_part, string $parsed_domain_part): void
    {
        if (strlen($local_part . '@' . $parsed_domain_part) > self::EMAIL_MAX_LENGTH) {
            $this->warnings[Email_Too_Long::CODE] = new Email_Too_Long();
        }
    }
}