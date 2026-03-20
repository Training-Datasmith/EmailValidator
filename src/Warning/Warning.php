<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Warning;

abstract class Warning implements \Stringable
{
    /**
     * @var int CODE
     */
    public const CODE = 0;
    /**
     * @var string
     */
    protected $message = '';
    /**
     * @var int
     */
    protected $rfc_number = 0;
    /**
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
    /**
     * @return int
     */
    public function code()
    {
        return self::CODE;
    }
    /**
     * @return int
     */
    public function rfc_number()
    {
        return $this->rfc_number;
    }
    public function __toString(): string
    {
        return $this->message() . ' rfc: ' . $this->rfc_number . 'internal code: ' . static::CODE;
    }
}