<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result;

interface Result
{
    /**
     * Is validation result valid?
     *
     */
    public function is_valid(): bool;
    /**
     * Is validation result invalid?
     * Usually the inverse of isValid()
     *
     */
    public function is_invalid(): bool;
    /**
     * Short description of the result, human readable.
     *
     */
    public function description(): string;
    /**
     * Code for user land to act upon.
     *
     */
    public function code(): int;
}