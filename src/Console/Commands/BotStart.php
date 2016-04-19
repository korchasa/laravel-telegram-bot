<?php

namespace korchasa\LaravelTelegramBot\Console\Commands;

use App\Bot;
use Illuminate\Console\Command;
use URL;

class BotStart extends Command
{
    /** @var string */
    protected $name = 'telegram:start';

    /** @var string */
    protected $description = 'Start sending updates to bot';

    public function handle()
    {
        /** @var Bot $bot */
        $bot = app('Bot');
        $bot->getTelegram()->setWebhook(
            URL::to('/telegram-bot/'.config('telegram-bot.callback_token'))
        );
    }
}
