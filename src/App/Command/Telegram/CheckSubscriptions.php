<?php

namespace App\Command\Telegram;

use App\Command\Signals;
use Obokaman\StockForecast\Domain\Model\Date\Interval;
use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\Stock;
use Obokaman\StockForecast\Domain\Model\Subscriber\SubscriberRepository;
use Obokaman\StockForecast\Domain\Service\Signal\CalculateScore;
use Obokaman\StockForecast\Domain\Service\Signal\GetSignalsFromMeasurements;
use Obokaman\StockForecast\Infrastructure\Http\StockMeasurement\Collector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Client as TelegramClient;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;

class CheckSubscriptions extends Command
{
    private const DEFAULT_SCORE_THRESHOLD = 3;

    private $stock_measurements_collector;
    private $get_signals_service;
    private $subscriber_repository;
    private $input;
    private $output;
    /** @var TelegramClient|BotApi */
    private $bot;

    public function __construct(
        Collector $a_stock_measurements_collector,
        GetSignalsFromMeasurements $a_get_signals_service,
        SubscriberRepository $a_subscriber_repository
    ) {
        $this->stock_measurements_collector = $a_stock_measurements_collector;
        $this->get_signals_service          = $a_get_signals_service;
        $this->subscriber_repository        = $a_subscriber_repository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('forecast:subscriptions')
             ->setDescription('Inform all Telegram subscribers.')
             ->setHelp('This command allow you to check current subscribers and inform them of relevant short-term information')
             ->addArgument('telegram_message_id', InputArgument::OPTIONAL, 'The currency code.')
             ->addOption('score_threshold', 's', InputOption::VALUE_OPTIONAL, self::DEFAULT_SCORE_THRESHOLD);
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->input    = $input;
        $this->output   = $output;
        $this->bot      = new TelegramClient($_SERVER['TELEGRAM_BOT_TOKEN']);
        $subscriber_ids = [];
        $pairs          = [];

        $score_threshold  = $this->input->getOption('score_threshold') ?: self::DEFAULT_SCORE_THRESHOLD;
        $telegram_chat_id = $this->input->getArgument('telegram_message_id');

        if (!empty($telegram_chat_id)) {
            $subscriber_ids[]         = $telegram_chat_id;
            $pairs[$telegram_chat_id] = Signals::DEFAULT_PAIRS;
        } else {
            $subscribers = $this->subscriber_repository->findAll();
            foreach ($subscribers as $subscriber) {
                $subscriber_id    = $subscriber->chatId()->id();
                $subscriber_ids[] = $subscriber_id;
                $subscriptions    = $subscriber->subscriptions();
                foreach ($subscriptions as $subscription) {
                    $pairs[$subscriber_id][] = [(string)$subscription->currency(), (string)$subscription->stock()];
                }
                $pairs[$subscriber_id] = Signals::DEFAULT_PAIRS;
            }
        }

        foreach ($subscriber_ids as $subscriber) {
            $this->checkSubscribedAlerts($subscriber, $pairs[$subscriber], $score_threshold);
        }
    }

    /**
     * @param       $telegram_message_id
     * @param array $pairs
     * @param int   $score_threshold
     */
    protected function checkSubscribedAlerts($telegram_message_id, array $pairs, int $score_threshold): void
    {
        foreach ($pairs as $pair) {
            try {
                [$currency, $stock] = $pair;

                $measurements = $this->stock_measurements_collector->getMeasurements(Currency::fromCode($currency),
                                                                                     Stock::fromCode($stock),
                                                                                     Interval::fromStringDateInterval('minutes'));

                $signals = $this->get_signals_service->getSignals($measurements);

                $score = CalculateScore::calculate(...$signals);

                if ($score <= $score_threshold && $score >= -$score_threshold) {
                    continue;
                }

                $message = 'Signals for *' . $currency . '-' . $stock . '* in *last 60 minutes* (Score: ' . $score . '):' . PHP_EOL;
                foreach ($signals as $signal) {
                    $message .= '- _' . $signal . '_' . PHP_EOL;
                }
                $message .= 'Now selling at *' . $measurements->end()->close() . ' ' . $currency . '*';

                $this->bot->sendMessage($telegram_message_id,
                                        $message,
                                        'Markdown',
                                        false,
                                        null,
                                        new InlineKeyboardMarkup([
                                                                     [
                                                                         [
                                                                             'text' => 'View ' . $currency . '-' . $stock . ' chart online',
                                                                             'url'  => 'https://www.cryptocompare.com/coins/' . strtolower($stock) . '/charts/' . strtolower($currency)
                                                                         ]
                                                                     ]
                                                                 ]));
            } catch (\Exception $e) {
                $this->output->writeln('There was an error: [' . \get_class($e) . '] ' . $e->getMessage());
            }
        }
    }
}
