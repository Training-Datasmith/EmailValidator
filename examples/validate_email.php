<?php

declare(strict_types=1);

/**
 * Example: Validate email addresses using various strategies.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Egulias\Email_Validator\Email_Validator;
use Egulias\Email_Validator\Validation\RFC_Validation;
use Egulias\Email_Validator\Validation\DNS_Check_Validation;
use Egulias\Email_Validator\Validation\No_RFC_Warnings_Validation;
use Egulias\Email_Validator\Validation\Multiple_Validation_With_And;

$validator = new Email_Validator();

$emails = [
    'user@example.com',
    'user+tag@sub.domain.org',
    '"quoted local"@example.com',  // valid RFC but unusual
    'no-at-sign',
    'missing@',
    '@no-local.com',
    'double..dot@example.com',
];

echo "=== RFC 5321/5322 Validation ===" . PHP_EOL;
foreach ($emails as $email) {
    $valid = $validator->is_valid($email, new RFC_Validation());
    $status = $valid ? 'VALID' : 'INVALID: ' . ($validator->get_error()?->description() ?? 'unknown');
    $warnings = $validator->has_warnings() ? ' [' . count($validator->get_warnings()) . ' warning(s)]' : '';
    printf("  %-40s %s%s\n", $email, $status, $warnings);
}

echo PHP_EOL . "=== Strict (no RFC warnings allowed) ===" . PHP_EOL;
$strictEmail = '"quoted"@example.com';
$valid = $validator->is_valid($strictEmail, new No_RFC_Warnings_Validation());
echo "  {$strictEmail}: " . ($valid ? 'VALID' : 'INVALID') . PHP_EOL;

echo PHP_EOL . "=== RFC + DNS check (requires network) ===" . PHP_EOL;
$dnsEmail = 'user@gmail.com';
$strategy = new Multiple_Validation_With_And([
    new RFC_Validation(),
    new DNS_Check_Validation(),
]);
$valid = $validator->is_valid($dnsEmail, $strategy);
echo "  {$dnsEmail}: " . ($valid ? 'VALID (MX found)' : 'INVALID') . PHP_EOL;
