<?php

declare (strict_types=1);
namespace Egulias\Email_Validator;

use Egulias\Email_Validator\Parser\Id_Left_Part;
use Egulias\Email_Validator\Parser\Id_Right_Part;
use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Result\Reason\No_Local_Part;
use Egulias\Email_Validator\Result\Result;
use Egulias\Email_Validator\Result\Valid_Email;
use Egulias\Email_Validator\Warning\Email_Too_Long;
class Message_Id_Parser extends Parser
{
    public const EMAILID_MAX_LENGTH = 254;
    /**
     * @var string
     */
    protected $id_left = '';
    /**
     * @var string
     */
    protected $id_right = '';
    public function parse(string $str): Result
    {
        $result = parent::parse($str);
        $this->add_long_email_warning($this->id_left, $this->id_right);
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
        return $this->process_id_left();
    }
    protected function parse_right_from_at(): Result
    {
        return $this->process_id_right();
    }
    private function process_id_left(): Result
    {
        $local_part_parser = new Id_Left_Part($this->lexer);
        $local_part_result = $local_part_parser->parse();
        $this->id_left = $local_part_parser->local_part();
        $this->warnings = [...$local_part_parser->get_warnings(), ...$this->warnings];
        return $local_part_result;
    }
    private function process_id_right(): Result
    {
        $domain_part_parser = new Id_Right_Part($this->lexer);
        $domain_part_result = $domain_part_parser->parse();
        $this->id_right = $domain_part_parser->domain_part();
        $this->warnings = [...$domain_part_parser->get_warnings(), ...$this->warnings];
        return $domain_part_result;
    }
    public function get_left_part(): string
    {
        return $this->id_left;
    }
    public function get_right_part(): string
    {
        return $this->id_right;
    }
    private function add_long_email_warning(string $local_part, string $parsed_domain_part): void
    {
        if (strlen($local_part . '@' . $parsed_domain_part) > self::EMAILID_MAX_LENGTH) {
            $this->warnings[Email_Too_Long::CODE] = new Email_Too_Long();
        }
    }
}