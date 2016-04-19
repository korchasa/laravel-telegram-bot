<?php

namespace korchasa\LaravelTelegramBot\Console\Commands;

use App\Bot;
use Illuminate\Console\Command;

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
        /** @var Bot $bot */
        $bot = app('Bot');
        $bot->getTelegram()->setWebhook('');
    }
}
