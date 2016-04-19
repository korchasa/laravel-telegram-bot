<?php

use Illuminate\Http\Request;
use korchasa\Telegram\Update;

Route::post('/telegram-bot/{token}', function (Request $request) {

    if ($request->get('token') === config('telegram-bot.callback_token')) {
        abort(403);
    }

    $update_data = json_decode($request->getContent());
    if (false === $update_data) {
        throw new InvalidArgumentException(
            "Can't parse telegram response: ".json_last_error_msg()
        );
    }

    app('Bot')->handle(new Update($update_data));
});
