<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Parser;

use Egulias\Email_Validator\Email_Lexer;
use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Result\Reason\Cr_No_Lf;
use Egulias\Email_Validator\Result\Reason\Expecting_Dtext;
use Egulias\Email_Validator\Result\Reason\Unusual_Elements;
use Egulias\Email_Validator\Result\Result;
use Egulias\Email_Validator\Result\Valid_Email;
use Egulias\Email_Validator\Warning\Address_Literal;
use Egulias\Email_Validator\Warning\Cfws_With_Fws;
use Egulias\Email_Validator\Warning\Domain_Literal as WarningDomainLiteral;
use Egulias\Email_Validator\Warning\Ipv6bad_Char;
use Egulias\Email_Validator\Warning\Ipv6colon_End;
use Egulias\Email_Validator\Warning\Ipv6colon_Start;
use Egulias\Email_Validator\Warning\IPV6Deprecated;
use Egulias\Email_Validator\Warning\Ipv6double_Colon;
use Egulias\Email_Validator\Warning\Ipv6group_Count;
use Egulias\Email_Validator\Warning\Ipv6max_Groups;
use Egulias\Email_Validator\Warning\Obsolete_Dtext;
class Domain_Literal extends Part_Parser
{
    public const IPV4_REGEX = '/\b(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/';
    public const OBSOLETE_WARNINGS = [Email_Lexer::INVALID, Email_Lexer::C_DEL, Email_Lexer::S_LF, Email_Lexer::S_BACKSLASH];
    public function parse(): Result
    {
        $this->add_tag_warnings();
        $i_pv6tag = false;
        $address_literal = '';
        do {
            if ($this->lexer->current->is_a(Email_Lexer::C_NUL)) {
                return new Invalid_Email(new Expecting_Dtext(), $this->lexer->current->value);
            }
            $this->add_obsolete_warnings();
            if ($this->lexer->is_next_token_any([Email_Lexer::S_OPENBRACKET, Email_Lexer::S_OPENBRACKET])) {
                return new Invalid_Email(new Expecting_Dtext(), $this->lexer->current->value);
            }
            if ($this->lexer->is_next_token_any([Email_Lexer::S_HTAB, Email_Lexer::S_SP, Email_Lexer::CRLF])) {
                $this->warnings[Cfws_With_Fws::CODE] = new Cfws_With_Fws();
                $this->parse_fws();
            }
            if ($this->lexer->is_next_token(Email_Lexer::S_CR)) {
                return new Invalid_Email(new Cr_No_Lf(), $this->lexer->current->value);
            }
            if ($this->lexer->current->is_a(Email_Lexer::S_BACKSLASH)) {
                return new Invalid_Email(new Unusual_Elements($this->lexer->current->value), $this->lexer->current->value);
            }
            if ($this->lexer->current->is_a(Email_Lexer::S_IPV6TAG)) {
                $i_pv6tag = true;
            }
            if ($this->lexer->current->is_a(Email_Lexer::S_CLOSEBRACKET)) {
                break;
            }
            $address_literal .= $this->lexer->current->value;
        } while ($this->lexer->move_next());
        //Encapsulate
        $address_literal = str_replace('[', '', $address_literal);
        $is_address_literal_i_pv4 = $this->check_ipv4tag($address_literal);
        if (!$is_address_literal_i_pv4) {
            return new Valid_Email();
        }
        $address_literal = $this->convert_i_pv4to_i_pv6($address_literal);
        if (!$i_pv6tag) {
            $this->warnings[Warning_Domain_Literal::CODE] = new Warning_Domain_Literal();
            return new Valid_Email();
        }
        $this->warnings[Address_Literal::CODE] = new Address_Literal();
        $this->check_ipv6tag($address_literal);
        return new Valid_Email();
    }
    /**
     * @param string $addressLiteral
     * @param int $maxGroups
     */
    public function check_ipv6tag($address_literal, $max_groups = 8): void
    {
        $prev = $this->lexer->get_previous();
        if ($prev->is_a(Email_Lexer::S_COLON)) {
            $this->warnings[Ipv6colon_End::CODE] = new Ipv6colon_End();
        }
        $i_pv6 = substr($address_literal, 5);
        //Daniel Marschall's new IPv6 testing strategy
        $matches_ip = explode(':', $i_pv6);
        $group_count = count($matches_ip);
        $colons = strpos($i_pv6, '::');
        if (count(preg_grep('/^[0-9A-Fa-f]{0,4}$/', $matches_ip, PREG_GREP_INVERT)) !== 0) {
            $this->warnings[Ipv6bad_Char::CODE] = new Ipv6bad_Char();
        }
        if ($colons === false) {
            // We need exactly the right number of groups
            if ($group_count !== $max_groups) {
                $this->warnings[Ipv6group_Count::CODE] = new Ipv6group_Count();
            }
            return;
        }
        if ($colons !== strrpos($i_pv6, '::')) {
            $this->warnings[Ipv6double_Colon::CODE] = new Ipv6double_Colon();
            return;
        }
        if ($colons === 0 || $colons === strlen($i_pv6) - 2) {
            // RFC 4291 allows :: at the start or end of an address
            //with 7 other groups in addition
            ++$max_groups;
        }
        if ($group_count > $max_groups) {
            $this->warnings[Ipv6max_Groups::CODE] = new Ipv6max_Groups();
        } elseif ($group_count === $max_groups) {
            $this->warnings[IPV6Deprecated::CODE] = new IPV6Deprecated();
        }
    }
    public function convert_i_pv4to_i_pv6(string $address_literal_i_pv4): string
    {
        $matches_ip = [];
        $i_pv4match = preg_match(self::IPV4_REGEX, $address_literal_i_pv4, $matches_ip);
        // Extract IPv4 part from the end of the address-literal (if there is one)
        if ($i_pv4match > 0) {
            $index = (int) strrpos($address_literal_i_pv4, $matches_ip[0]);
            //There's a match but it is at the start
            if ($index > 0) {
                // Convert IPv4 part to IPv6 format for further testing
                return substr($address_literal_i_pv4, 0, $index) . '0:0';
            }
        }
        return $address_literal_i_pv4;
    }
    /**
     * @param string $addressLiteral
     */
    protected function check_ipv4tag($address_literal): bool
    {
        $matches_ip = [];
        $i_pv4match = preg_match(self::IPV4_REGEX, $address_literal, $matches_ip);
        // Extract IPv4 part from the end of the address-literal (if there is one)
        if ($i_pv4match > 0) {
            $index = strrpos($address_literal, $matches_ip[0]);
            //There's a match but it is at the start
            if ($index === 0) {
                $this->warnings[Address_Literal::CODE] = new Address_Literal();
                return false;
            }
        }
        return true;
    }
    private function add_obsolete_warnings(): void
    {
        if (in_array($this->lexer->current->type, self::OBSOLETE_WARNINGS)) {
            $this->warnings[Obsolete_Dtext::CODE] = new Obsolete_Dtext();
        }
    }
    private function add_tag_warnings(): void
    {
        if ($this->lexer->is_next_token(Email_Lexer::S_COLON)) {
            $this->warnings[Ipv6colon_Start::CODE] = new Ipv6colon_Start();
        }
        if ($this->lexer->is_next_token(Email_Lexer::S_IPV6TAG)) {
            $lexer = clone $this->lexer;
            $lexer->move_next();
            if ($lexer->is_next_token(Email_Lexer::S_DOUBLECOLON)) {
                $this->warnings[Ipv6colon_Start::CODE] = new Ipv6colon_Start();
            }
        }
    }
}