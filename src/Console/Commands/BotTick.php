<?php

namespace korchasa\LaravelTelegramBot\Console\Commands;

use App\Bot;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository;
use korchasa\LaravelTelegramBot\BaseBot;
use korchasa\Telegram\Telegram;
use Symfony\Component\Console\Input\InputArgument;

class BotTick extends Command
{
    /** @var string */
    protected $name = 'telegram:tick';

    /** @var string */
    protected $description = 'Process recent updates for bot and store last id in cache';

    const CACHE_KEY = 'last_update';

    /**
     * Execute the console command.
     *
     * @param Repository $cache
     *
     * @return mixed
     */
    public function handle(Repository $cache)
    {
        $iterations_count = $this->argument('iterations') ?: 1;
        /** @var BaseBot $bot */
        $bot = app('Bot');

        while ($iterations_count--) {
            $updatesChunk = $bot->getTelegram()->getUpdates($cache->get(self::CACHE_KEY) + 1, null, 3);
            foreach ($updatesChunk as $update) {
                $bot->handle($update);
                $cache->forever(self::CACHE_KEY, $update->update_id);
            }
        }
    }

    protected function getArguments()
    {
        return [
            ['iterations', InputArgument::OPTIONAL, 'iterations'],
        ];
    }
}
