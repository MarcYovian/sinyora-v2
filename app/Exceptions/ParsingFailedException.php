<?php

namespace App\Exceptions;

use Exception;

class ParsingFailedException extends Exception
{
    protected $field;

    public function __construct($message = "Parsing failed", $field)
    {
        parent::__construct($message);
        $this->field = $field;
    }

    public function getField(): string
    {
        return $this->field;
    }
}
