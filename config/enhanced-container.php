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
     |
     | Available naming options: plural, pluralStudly, singular, studly,
     | any other string conversion from "Illuminate\Support\Str"
     |
     | Default: 'pluralStudly'
     */

    'from' => [
        'layer'  => 'Service',
        'naming' => 'pluralStudly',
    ],

    /*
     | "To"
     |
     | The layer where the call is forwarded.
     |
     | Available naming options: plural, pluralStudly, singular, studly,
     | any other string conversion from "Illuminate\Support\Str"
     |
     | Default: 'pluralStudly'
     */

    'to' => [
        'layer'  => 'Repository',
        'naming' => 'pluralStudly',
    ],

];
