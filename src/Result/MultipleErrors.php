<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result;

use Egulias\Email_Validator\Result\Reason\Empty_Reason;
use Egulias\Email_Validator\Result\Reason\Reason;
/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class Multiple_Errors extends Invalid_Email
{
    /**
     * @var Reason[]
     */
    private array $reasons = [];
    public function __construct()
    {
    }
    public function add_reason(Reason $reason): void
    {
        $this->reasons[$reason->code()] = $reason;
    }
    /**
     * @return Reason[]
     */
    public function get_reasons(): array
    {
        return $this->reasons;
    }
    public function reason(): Reason
    {
        return 0 !== count($this->reasons) ? current($this->reasons) : new Empty_Reason();
    }
    public function description(): string
    {
        $description = '';
        foreach ($this->reasons as $reason) {
            $description .= $reason->description() . PHP_EOL;
        }
        return $description;
    }
    public function code(): int
    {
        return 0;
    }
}