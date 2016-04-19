<?php

namespace korchasa\LaravelTelegramBot\Console\Commands;

use Illuminate\Console\Command;
use korchasa\Telegram\Telegram;

class BotStop extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'telegram:stop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Stop sending updates to bot';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $telegram = new Telegram(env('TELEGRAM_BOT_TOKEN'));
        $telegram->setWebhook('');
    }
}
