<?php

namespace korchasa\LaravelTelegramBot;

use Finite\StatefulInterface;
use Illuminate\Cache\CacheManager;
use Illuminate\Session\SessionManager;
use Illuminate\Support\Collection;
use korchasa\Telegram\User;
use Symfony\Component\HttpFoundation\Session\Session;
use DB;

class Context implements StatefulInterface
{
    const TABLE = 'telegram_bot_context';

    /** @var User */
    public $user;

    /** @var string */
    public $state;

    /** @var Collection|array */
    public $params;

    /** @var array */
    public $lastMessage;

    public function __construct(
        User $user,
        $state = null,
        $params = [],
        $lastMessage = null
    ) {
        $this->user = $user;
        $this->state = $state ?: 'start';
        $this->params = new Collection($params);
        $this->lastMessage = $lastMessage;
    }

    /**
     * @param $userId
     *
     * @return $this
     * @throws \RuntimeException
     */
    public static function findByUserId($userId)
    {
        $contextData = head(DB::select(
            'SELECT * FROM '.static::TABLE.' WHERE user_id = :user_id',
            ['user_id' => $userId]
        ));

        if ($contextData) {
            $user = new User($contextData);
            return new Context(
                $user,
                $contextData->state,
                json_decode($contextData->params),
                json_decode($contextData->last_message)
            );
        }
    }

    public function save()
    {
        $contextData = [
            'user_id' => $this->user->user_id,
            'first_name' => $this->user->first_name,
            'last_name' => $this->user->last_name,
            'username' => $this->user->username,
            'state' => $this->state,
            'params' => json_encode($this->params),
            'last_message' => json_encode($this->lastMessage)
        ];

        if (!static::findByUserId($this->user->user_id)) {
            DB::insert(
                'insert into '.static::TABLE.' (user_id, first_name, last_name, username, state, params, last_message) values (:user_id, :first_name, :last_name, :username, :state, :params, :last_message)',
                $contextData
            );
        } else {
            DB::update(
                'update '.static::TABLE.' set first_name = :first_name, last_name = :last_name, username = :username, state = :state, params = :params, last_message = :last_message where user_id = :user_id',
                $contextData
            );
        }
    }

    /**
     * @return string
     */
    public function getFiniteState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setFiniteState($state)
    {
        $this->state = $state;
    }
}
