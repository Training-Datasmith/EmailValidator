<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Validation;

use Egulias\Email_Validator\Email_Lexer;
use Egulias\Email_Validator\Result\Invalid_Email;
use Egulias\Email_Validator\Result\Reason\Domain_Accepts_No_Mail;
use Egulias\Email_Validator\Result\Reason\Local_Or_Reserved_Domain;
use Egulias\Email_Validator\Result\Reason\No_Dns_Record as ReasonNoDNSRecord;
use Egulias\Email_Validator\Result\Reason\Unable_To_Get_Dns_Record;
use Egulias\Email_Validator\Warning\No_Dnsmx_Record;
use Egulias\Email_Validator\Warning\Warning;
class Dns_Check_Validation implements Email_Validation
{
    /**
     * Reserved Top Level DNS Names (https://tools.ietf.org/html/rfc2606#section-2),
     * mDNS and private DNS Namespaces (https://tools.ietf.org/html/rfc6762#appendix-G)
     *
     * @var string[]
     */
    public const RESERVED_DNS_TOP_LEVEL_NAMES = [
        // Reserved Top Level DNS Names
        'test',
        'example',
        'invalid',
        'localhost',
        // mDNS
        'local',
        // Private DNS Namespaces
        'intranet',
        'internal',
        'private',
        'corp',
        'home',
        'lan',
    ];
    /**
     * @var Warning[]
     */
    private array $warnings = [];
    private ?\Egulias\Email_Validator\Result\Invalid_Email $error = null;
    private array $mx_records = [];
    private readonly ?\Egulias\Email_Validator\Validation\Dns_Get_Record_Wrapper $dns_get_record;
    public function __construct(?Dns_Get_Record_Wrapper $dns_get_record = null)
    {
        if (!function_exists('idn_to_ascii')) {
            throw new \LogicException(sprintf('The %s class requires the Intl extension.', self::class));
        }
        if ($dns_get_record == null) {
            $dns_get_record = new Dns_Get_Record_Wrapper();
        }
        $this->dns_get_record = $dns_get_record;
    }
    public function is_valid(string $email, Email_Lexer $email_lexer): bool
    {
        // use the input to check DNS if we cannot extract something similar to a domain
        $host = $email;
        // Arguable pattern to extract the domain. Not aiming to validate the domain nor the email
        if (false !== $last_at_pos = strrpos($email, '@')) {
            $host = substr($email, $last_at_pos + 1);
        }
        // Get the domain parts
        $host_parts = explode('.', $host);
        $is_local_domain = count($host_parts) <= 1;
        $is_reserved_top_level = in_array($host_parts[count($host_parts) - 1], self::RESERVED_DNS_TOP_LEVEL_NAMES, true);
        // Exclude reserved top level DNS names
        if ($is_local_domain || $is_reserved_top_level) {
            $this->error = new Invalid_Email(new Local_Or_Reserved_Domain(), $host);
            return false;
        }
        return $this->check_dns($host);
    }
    public function get_error(): ?Invalid_Email
    {
        return $this->error;
    }
    /**
     * @return Warning[]
     */
    public function get_warnings(): array
    {
        return $this->warnings;
    }
    /**
     * @param string $host
     */
    protected function check_dns($host): bool
    {
        $variant = INTL_IDNA_VARIANT_UTS46;
        $host = rtrim(idn_to_ascii($host, IDNA_DEFAULT, $variant), '.');
        $host_parts = explode('.', $host);
        $host = array_pop($host_parts);
        while (count($host_parts) > 0) {
            $host = array_pop($host_parts) . '.' . $host;
            if ($this->validate_dns_records($host)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Validate the DNS records for given host.
     *
     * @param string $host A set of DNS records in the format returned by dns_get_record.
     *
     * @return bool True on success.
     */
    private function validate_dns_records(string $host): bool
    {
        $dns_records_result = $this->dns_get_record->get_records($host, DNS_A + DNS_MX);
        if ($dns_records_result->with_error()) {
            $this->error = new Invalid_Email(new Unable_To_Get_Dns_Record(), '');
            return false;
        }
        $dns_records = $dns_records_result->get_records();
        // Combined check for A+MX+AAAA can fail with SERVFAIL, even in the presence of valid A/MX records
        $aaaa_records_result = $this->dns_get_record->get_records($host, DNS_AAAA);
        if (!$aaaa_records_result->with_error()) {
            $dns_records = array_merge($dns_records, $aaaa_records_result->get_records());
        }
        // No MX, A or AAAA DNS records
        if ($dns_records === []) {
            $this->error = new Invalid_Email(new Reason_No_Dns_Record(), '');
            return false;
        }
        // For each DNS record
        foreach ($dns_records as $dns_record) {
            if (!$this->validate_mx_record($dns_record)) {
                // No MX records (fallback to A or AAAA records)
                if (empty($this->mx_records)) {
                    $this->warnings[No_Dnsmx_Record::CODE] = new No_Dnsmx_Record();
                }
                return false;
            }
        }
        return true;
    }
    /**
     * Validate an MX record
     *
     * @param array $dnsRecord Given DNS record.
     *
     * @return bool True if valid.
     */
    private function validate_mx_record(array $dns_record): bool
    {
        if (!isset($dns_record['type'])) {
            $this->error = new Invalid_Email(new Reason_No_Dns_Record(), '');
            return false;
        }
        if ($dns_record['type'] !== 'MX') {
            return true;
        }
        // "Null MX" record indicates the domain accepts no mail (https://tools.ietf.org/html/rfc7505)
        if (empty($dns_record['target']) || $dns_record['target'] === '.') {
            $this->error = new Invalid_Email(new Domain_Accepts_No_Mail(), '');
            return false;
        }
        $this->mx_records[] = $dns_record;
        return true;
    }
}