# laravel-telegram-bot

```php
<?php namespace App;

use korchasa\LaravelTelegramBot\BaseBot;
use Finite\State\StateInterface;
use korchasa\Telegram\Update;

class ExampleBot extends BaseBot
{
    /**
     * @throws \Finite\Exception\StateException
     */
    public function start()
    {
        $this->sendMessage('What\'s your name?');
        $this->transition('wait_for_name');
    }
    
    public function wait_for_name(Update $update) {
        if ($update === 'korchasa') {
            $this->transition('special_name_entered');
        } else {
            $this->transition('name_entered');
        }
    }
    
    public function hello()
    {
        $this->sendMessage('What\'s your name?');
    }
    
    public function states()
    {
        return [
            'start' => [
                'type' => StateInterface::TYPE_INITIAL,
            ],
            'wait_for_name'  => [
                'type' => StateInterface::TYPE_NORMAL,
            ],
            'hello'  => [
                'type' => StateInterface::TYPE_FINAL,
            ],
        ];
    }
    
    public function transitions()
    {
        return [
            'wait_for_name' => ['from' => ['start'], 'to' => 'wait_for_name'],
            'name_entered' => ['from' => ['wait_for_name'], 'to' => 'hello'],
        ];
    }
}
```
