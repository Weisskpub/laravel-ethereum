<?php

namespace Weisskpub\Ethereum\Exceptions;

use RuntimeException;

class EthereumdException extends RuntimeException
{
    /**
     * Construct new bitcoincashd exception.
     *
     * @param object $error
     *
     * @return void
     */
    public function __construct($error)
    {
        parent::__construct($error['message'], $error['code']);
    }
}
