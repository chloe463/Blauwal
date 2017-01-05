<?php

namespace chloe463\Blauwal;

class Exception extends \Exception
{
    const PARAMETER_ERROR = 1;

    /**
     * Constructor
     */
    public function __construct($message, $code, \Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
