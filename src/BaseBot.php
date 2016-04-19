<?php

namespace korchasa\LaravelTelegramBot;

use Log;
use Finite\Loader\ArrayLoader;
use Finite\StateMachine\StateMachine;
use GuzzleHttp\Exception\ClientException;
use korchasa\Telegram\Botan;
use korchasa\Telegram\ForceReply;
use korchasa\Telegram\ReplyKeyboardHide;
use korchasa\Telegram\ReplyKeyboardMarkup;
use korchasa\Telegram\Telegram;
use korchasa\Telegram\Update;
use TwigBridge\Extension\Laravel\Session;

abstract class BaseBot
{
    /** @var StateMachine */
    protected $stateMachine;

    /** @var Telegram */
    protected $telegram;

    /** @var Context */
    protected $context;

    /** @var Botan */
    protected $botan;

    protected $sendCurrentStage = false;

    public function __construct($telegram, $botan = null)
    {
        $this->telegram = $telegram;
        $this->botan = $botan;
        $this->stateMachine = new StateMachine();
        $loader = new ArrayLoader(
            [
                'class'       => Context::class,
                'states'      => $this->states(),
                'transitions' => $this->transitions(),
            ]
        );
        $loader->load($this->stateMachine);
    }

    /**
     * @return array
     */
    abstract public function states();

    /**
     * @return array
     */
    abstract public function transitions();

    public function stateMachine()
    {
        return $this->stateMachine;
    }

    public function applyContext(Context $context)
    {
        $this->context = $context;
        $this->stateMachine->setObject($context);
        $this->stateMachine->initialize();
    }

    public function context()
    {
        return $this->context;
    }

    /**
     * @param Update $update
     *
     * @throws \Exception
     */
    public function handle(Update $update)
    {
        $user = $update->message->from;

        $this->applyContext(
            Context::findByUserId($user->user_id) ?: new Context($user)
        );

        $state = $this->stateMachine->getCurrentState()->getName();

        if ($this->sendCurrentStage) {
            $this->sendMessage('Current stage: '.$state);
        }

        $this->tryAnswerForCommand($update);

        try {
            $this->$state($update);
        } catch (ClientException $e) {
            $this->telegram->sendMessage(
                $update->message->from,
                'Произошла внутреняя ошибка'
            );
            Log::info($e);
        }

        $this->context->save();

        if ($this->botan) {
            $this->botan->track($update->message);
        }
    }

    /**
     * @param                                                       $text
     * @param ReplyKeyboardMarkup|ReplyKeyboardHide|ForceReply|null $reply_markup
     * @param null                                                  $reply_to_message_id
     * @param bool|false                                            $disable_web_page_preview
     *
     * @return mixed
     */
    protected function sendMessage(
        $text,
        $reply_markup = null,
        $reply_to_message_id = null,
        $disable_web_page_preview = true
    ) {
        try {
            $result = $this->telegram->sendMessage(
                $this->context->user,
                $text,
                $reply_markup,
                $reply_to_message_id,
                $disable_web_page_preview
            );
            $this->context->lastMessage = func_get_args();

            return $result;
        } catch (ClientException $e) {
            $response_obj = json_decode($e->getResponse()->getBody());
            if (429 === $e->getResponse()->getStatusCode()) {
                sleep(5);

                return $this->sendMessage(
                    $text,
                    $reply_markup,
                    $reply_to_message_id,
                    $disable_web_page_preview
                );
            } elseif ($response_obj && 'Error: PEER_ID_INVALID' === $response_obj->description) {
                Log::warning('Error on sendMessage: '.json_encode($response_obj));
            } else {
                throw $e;
            }
        }

        return null;
    }

    protected function tryAnswerForCommand(Update $update)
    {
        if (!$update->isText()) {
            return null;
        }

        foreach ($this->transitions() as $transition_name => $transition_options) {
            $isValidTransition = starts_with($update->message->text, '/'.$transition_name)
                && $this->stateMachine->can($transition_name);
            if ($isValidTransition) {
                return $this->transition($transition_name);
            }
        }

        return null;
    }

    /**
     * @return mixed
     */
    protected function resendLastMessage()
    {
        if (!$this->context->lastMessage) {
            return null;
        }

        return call_user_func_array([$this, 'sendMessage'], $this->context->lastMessage);
    }

    /**
     * @param $name
     *
     * @return mixed
     * @throws \Finite\Exception\StateException
     */
    public function transition($name)
    {
        return $this->stateMachine->apply($name);
    }

    public function getTelegram()
    {
        return $this->telegram;
    }
}
