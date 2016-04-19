<?php

return [
    'debug'           => true,
    'bot_class'       => \App\Bot::class,
    'token'           => env('TELEGRAM_BOT_TOKEN', ''), // 12345678:many-letter-and-digits
    'callback_token'  => env('TELEGRAM_CALLBACK_TOKEN', ''), // any-string
    'identifier'      => env('TELEGRAM_BOT_IDENTIFIER', ''), // string after @ sign
    'log'             => env('TELEGRAM_LOG', null)
];
