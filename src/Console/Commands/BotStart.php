<?php

namespace korchasa\LaravelTelegramBot\Console\Commands;

use Illuminate\Console\Command;
use korchasa\Telegram\Telegram;
use URL;

class BotStart extends Command
{
    /** @var string */
    protected $name = 'telegram:start';

    /** @var string */
    protected $description = 'Start sending updates to bot';

    public function handle()
    {
        $telegram = new Telegram(env('TELEGRAM_BOT_TOKEN'));
        $telegram->setWebhook(URL::to('/bot/'.env('APP_KEY')));
    }
}
