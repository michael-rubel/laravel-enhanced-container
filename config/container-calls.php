<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Method Forwarding
    |--------------------------------------------------------------------------
    |
    | This feature will help you forward your call to secondary service automatically
    | if the target class doesn't contain the method that was called. You'll be
    | able to configure method forwarding directories to meet your application
    | structure.
    |
    */

    /*
     | Determine if you want to use method forwarding.
     */

    'forwarding_enabled' => false,

    /*
     | "From"
     |
     | The layer from whom the call forwarded.
     */

    'from' => 'Service',

    /*
     | "To"
     |
     | The layer where the call is forwarded.
     */

    'to' => 'Repository',

];
