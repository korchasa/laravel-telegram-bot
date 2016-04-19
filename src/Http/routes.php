<?php

use Illuminate\Http\Request;
use korchasa\Telegram\Botan;
use korchasa\Telegram\Telegram;
use korchasa\Telegram\Update;

Route::post('/telegram-bot/{token}', function (Request $request) {

    $config = config('telegram-bot');

    if ($request->get('token') === $config['callback_token']) {
        abort(404);
    }

    $bot_class = $config['bot_class'];

    $bot = new $bot_class(
        new Telegram($config['token']),
        new Botan($config['botan_token'])
    );

    $update_data = json_decode($request->getContent());
    if (false === $update_data) {
        throw new InvalidArgumentException(
            "Can't parse telegram response: ".json_last_error_msg()
        );
    }

    $bot->handle(new Update($update_data));
});
