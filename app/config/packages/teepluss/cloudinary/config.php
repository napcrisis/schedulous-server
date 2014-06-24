<?php

return array(

    /*
    |--------------------------------------------------------------------------
    | Cloudinary API configuration
    |--------------------------------------------------------------------------
    |
    | Before using Cloudinary you need to register and get some detail
    | to fill in below, please visit cloudinary.com.
    |
    */

    'cloudName' => 'dmpqz7bjy',
    'baseUrl' => 'http://res.cloudinary.com/dmpqz7bj',
    'secureUrl' => 'https://res.cloudinary.com/dmpqz7bjy',
    'apiBaseUrl' => 'https://api.cloudinary.com/v1_1/dmpqz7bjy',
    'apiKey' => '633293346777846',
    'apiSecret' => 'mHd2ykVc9iOKVoJ-P8X-BGPoNaM',

    /*
    |--------------------------------------------------------------------------
    | Default image scaling to show.
    |--------------------------------------------------------------------------
    |
    | If you not pass options parameter to Cloudy::show the default
    | will be replaced.
    |
    */

    'scaling' => array(
        'format' => 'png',
        'with' => 150,
        'height' => 150,
        'crop' => 'fit',
        'effect' => null
    )

);