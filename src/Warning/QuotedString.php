<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Warning;

class Quoted_String extends Warning
{
    public const CODE = 11;
    /**
     * @param string|int $prevToken
     * @param string|int $postToken
     */
    public function __construct($prev_token, $post_token)
    {
        $this->message = "Quoted String found between {$prev_token} and {$post_token}";
    }
}