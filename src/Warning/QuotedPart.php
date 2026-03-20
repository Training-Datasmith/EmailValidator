<?php

declare (strict_types=1);
namespace Egulias\Email_Validator\Warning;

use Unit_Enum;
class Quoted_Part extends Warning
{
    public const CODE = 36;
    /**
     * @param UnitEnum|string|int|null $prevToken
     * @param UnitEnum|string|int|null $postToken
     */
    public function __construct($prev_token, $post_token)
    {
        if ($prev_token instanceof Unit_Enum) {
            $prev_token = $prev_token->name;
        }
        if ($post_token instanceof Unit_Enum) {
            $post_token = $post_token->name;
        }
        $this->message = "Deprecated Quoted String found between {$prev_token} and {$post_token}";
    }
}