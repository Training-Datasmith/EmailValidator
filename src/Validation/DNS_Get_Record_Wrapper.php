<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Validation;

class Dns_Get_Record_Wrapper
{
    public function get_records(string $host, int $type): Dns_Records
    {
        // A workaround to fix https://bugs.php.net/bug.php?id=73149
        set_error_handler(static function (int $error_level, string $error_message): never {
            throw new \RuntimeException("Unable to get DNS record for the host: {$error_message}");
        });
        try {
            // Get all MX, A and AAAA DNS records for host
            return new Dns_Records(dns_get_record($host, $type));
        } catch (\RuntimeException) {
            return new Dns_Records([], true);
        } finally {
            restore_error_handler();
        }
    }
}