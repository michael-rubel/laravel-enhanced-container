<?php

namespace MichaelRubel\EnhancedContainer\Exceptions;

class InstanceInteractionException extends \Exception
{
    protected $message = 'You was operating on different instance previously. Check your forwarding rules.';
}
