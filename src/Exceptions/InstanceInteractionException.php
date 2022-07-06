<?php

namespace MichaelRubel\EnhancedContainer\Exceptions;

class InstanceInteractionException extends \Exception
{
    protected $message = 'You\'re trying to operate on the new instance, but you were accessing a different instance previously. Check your forwarding rules.';
}
