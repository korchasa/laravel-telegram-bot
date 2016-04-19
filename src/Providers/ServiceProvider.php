<?php

namespace korchasa\LaravelTelegramBot\Providers;

use korchasa\LaravelTelegramBot\Console\Commands\BotStart;
use korchasa\LaravelTelegramBot\Console\Commands\BotStop;
use korchasa\LaravelTelegramBot\Console\Commands\BotTick;
use korchasa\Telegram\Telegram;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * @return void
     */
    public function boot()
    {
        if (! $this->app->routesAreCached()) {
            require __DIR__.'/../Http/routes.php';
        }

        $this->publishes([
            __DIR__.'/../config/telegram-bot.php' => config_path('telegram-bot.php'),
        ]);

        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'migrations');

    }

    /**
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/telegram-bot.php', 'telegram-bot'
        );

        $this->app->singleton(Telegram::class, function ($app) {
            return new Telegram(
                config('telegram-bot.token'),
                [],
                config('telegram-bot.log')
            );
        });

        $this->app->singleton('Bot', function ($app) {
            $class = config('telegram-bot.bot_class');
            return new $class(
                $app->make(Telegram::class)
            );
        });

        $this->app['command.telegram.tick'] = $this->app->share(function ($app) {
            return new BotTick();
        });

        $this->app['command.telegram.start'] = $this->app->share(function ($app) {
            return new BotStart();
        });

        $this->app['command.telegram.stop'] = $this->app->share(function ($app) {
            return new BotStop();
        });

        $this->commands(
            'command.telegram.tick',
            'command.telegram.start',
            'command.telegram.stop'
        );
    }
}
