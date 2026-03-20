<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Result\Reason;

class Comments_In_Id_Right implements Reason
{
    public function code(): int
    {
        return 400;
    }
    public function description(): string
    {
        return 'Comments are not allowed in IDRight for message-id';
    }
}