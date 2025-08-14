<?php

namespace App\Exceptions;

use Exception;

class ScheduleConflictException extends Exception
{
    public function __construct($message = "Schedule conflict detected.")
    {
        parent::__construct($message);
    }
}
