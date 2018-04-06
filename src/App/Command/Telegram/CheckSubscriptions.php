<?php

namespace App\Command\Telegram;

use App\Command\Signals;
use Obokaman\StockForecast\Application\Service\GetSignalsFromForecastRequest;
use Obokaman\StockForecast\Domain\Model\Date\Interval;
use Obokaman\StockForecast\Domain\Model\Financial\Currency;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\Stock;
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

    public function __construct(Collector $a_stock_measurements_collector, GetSignalsFromMeasurements $a_get_signals_service)
    {
        $this->stock_measurements_collector = $a_stock_measurements_collector;
        $this->get_signals_service          = $a_get_signals_service;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('forecast:subscriptions')
            ->setDescription('Inform all Telagram subscribers.')
            ->setHelp('This command allow you to check current subscribers and inform them of relevant short-term information')
            ->addArgument('telegram_message_id', InputArgument::OPTIONAL, 'The currency code.')
            ->addOption('score_threshold', 's', InputOption::VALUE_OPTIONAL, self::DEFAULT_SCORE_THRESHOLD);
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        /** @var TelegramClient|BotApi $bot */
        $bot = new TelegramClient($_SERVER['TELEGRAM_BOT_TOKEN']);

        try
        {
            $pairs = Signals::DEFAULT_PAIRS;
            foreach ($pairs as $pair)
            {
                [$currency, $stock] = $pair;

                $measurements = $this->stock_measurements_collector->getMeasurements(
                    Currency::fromCode($currency),
                    Stock::fromCode($stock),
                    Interval::fromStringDateInterval('minutes')
                );

                $signals         = $this->get_signals_service->getSignals($measurements);

                $score           = CalculateScore::calculate(...$signals);
                $score_threshold = $input->getOption('score_threshold') ?: self::DEFAULT_SCORE_THRESHOLD;

                if ($score <= $score_threshold && $score >= -$score_threshold)
                {
                    continue;
                }

                $message = 'Signals for *' . $currency . '-' . $stock . '* in *last 60 minutes* (Score: ' . $score . '):' . PHP_EOL;
                foreach ($signals as $signal)
                {
                    $message .= '- _' . $signal . '_' . PHP_EOL;
                }
                $message .= 'Now selling at *' . $measurements->end()->close() . ' ' . $currency . '*';

                $bot->sendMessage(
                    $input->getArgument('telegram_message_id'),
                    $message,
                    'Markdown',
                    false,
                    null,
                    new InlineKeyboardMarkup(
                        [
                            [
                                [
                                    'text' => 'View ' . $currency . '-' . $stock . ' chart online',
                                    'url'  => 'https://www.cryptocompare.com/coins/' . strtolower($stock) . '/charts/' . strtolower($currency)
                                ]
                            ]
                        ]
                    )
                );
            }
        }
        catch (\Exception $e)
        {
            $output->writeln('There was an error: [' . \get_class($e) . '] ' . $e->getMessage());
        }
    }
}
