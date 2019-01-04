<?php

namespace App\Controller\Telegram;

use App\Controller\Telegram\Command\BaseCommand;
use App\Controller\Telegram\Command\IsCommandSpecification;
use App\Controller\Telegram\Command\OpenTextInterpreter;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client;
use TelegramBot\Api\Types\Update;

final class Command
{
    private $telegram_client;

    /** @var BaseCommand[] */
    private $available_commands;

    public function __construct(Client $a_telegram_client, BaseCommand ...$telegram_commands)
    {
        $this->telegram_client    = $a_telegram_client;
        $this->available_commands = $telegram_commands;
    }

    public function configure(): void
    {
        $this->configureOpenTextAnswer();
        $this->configureAvailableCommands();
    }

    private function configureOpenTextAnswer(): void
    {
        $bot = $this->telegram_client;

        $this->telegram_client->on(
            function (Update $an_update) use ($bot) {
                $answer_message = (new OpenTextInterpreter())->answer($an_update);

                /** @var BotApi $bot */
                $bot->sendMessage($an_update->getMessage()->getChat()->getId(), $answer_message, 'Markdown');
            },
            function (Update $an_update) {
                return (new IsCommandSpecification())->isSatisfiedBy($an_update);
            }
        );
    }

    private function configureAvailableCommands(): void
    {
        foreach ($this->available_commands as $command) {
            $this->telegram_client->command($command->getCommand(), $command->getCallable());
        }
    }
}
