<?php

namespace Src;

use Telegram\Bot\Api;
use Throwable;

class Telegram
{
    private Api $telegram;
    private array $api_users;

    public function __construct(string $api_chat, array $api_users)
    {

        try {

            $this->telegram = new Api($api_chat);

            $this->api_users = $api_users;

        } catch (Throwable $e) {

            Log::error('error', 'Telegram not created throw api key' . $e->getMessage());

        }

    }

    public function send(string $message): bool
    {

        try {

            foreach ($this->api_users as $api_user)
                $this->telegram->sendMessage(['chat_id' => $api_user, 'text' => $message, 'parse_mode' => 'Markdown']);

        } catch (Throwable $e) {

            Log::error('error', 'Do not send message' . $e->getMessage());

            return false;

        }

        return true;

    }

}
