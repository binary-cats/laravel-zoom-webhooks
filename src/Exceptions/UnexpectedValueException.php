<?php

namespace BinaryCats\ZoomWebhooks\Exceptions;

use UnexpectedValueException as BaseUnexpectedValueException;

class UnexpectedValueException extends BaseUnexpectedValueException
{
    public function render($request)
    {
        return response(['error' => $this->getMessage()], 400);
    }
}
