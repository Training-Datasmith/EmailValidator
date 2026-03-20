<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Validation\Exception;

use Exception;
class Empty_Validation_List extends \InvalidArgumentException
{
    /**
     * @param int $code
     */
    public function __construct($code = 0, ?Exception $previous = null)
    {
        parent::__construct('Empty validation list is not allowed', $code, $previous);
    }
}