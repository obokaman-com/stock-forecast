<?php

namespace App\Command;

use Obokaman\StockForecast\Application\Service\PredictStockValue;
use Obokaman\StockForecast\Application\Service\PredictStockValueRequest;
use Obokaman\StockForecast\Domain\Model\Date\Interval;
use Obokaman\StockForecast\Domain\Service\Signal\CalculateScore;
use Obokaman\StockForecast\Domain\Service\Signal\GetSignalsFromMeasurements;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Signals extends Command
{
    public const DEFAULT_PAIRS = [
        ['EUR', 'BTC'],
        ['EUR', 'ETH'],
        ['EUR', 'XRP'],
        ['EUR', 'LTC']
    ];
    private $stock_predict_service;
    private $get_signals_service;
    /** @var InputInterface */
    private $input;
    /** @var OutputInterface */
    private $output;

    public function __construct(
        PredictStockValue $a_stock_predict_service,
        GetSignalsFromMeasurements $a_get_signals_service
    ) {
        $this->stock_predict_service = $a_stock_predict_service;
        $this->get_signals_service = $a_get_signals_service;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('forecast:signals')
             ->setDescription('Gives you some insights based on given crypto evolution in last days, hours and minutes.')
             ->setHelp('This command gives you some insights & signals for given currency/crypto pair.')
             ->addArgument('currency', InputArgument::OPTIONAL, 'The currency code.')
             ->addArgument('stock', InputArgument::OPTIONAL, 'The stock code.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        $pairs = self::DEFAULT_PAIRS;
        if (!empty($input->getArgument('currency')) && !empty($input->getArgument('stock'))) {
            $pairs = [[$input->getArgument('currency'), $input->getArgument('stock')]];
        }

        foreach ($pairs as $pair) {
            $this->outputPairSignals($pair[0], $pair[1]);
        }

        return 0;
    }

    private function outputPairSignals(string $currency, string $stock): void
    {
        $this->outputCommandTitle($currency, $stock);

        $this->outputSignalsBasedOn('hour', Interval::MINUTES, $currency, $stock);
        $this->outputSignalsBasedOn('day', Interval::HOURS, $currency, $stock);
        $this->outputSignalsBasedOn('month', Interval::DAYS, $currency, $stock);
    }

    private function outputCommandTitle(string $currency, string $stock): void
    {
        $this->output->writeln(sprintf('<options=bold>===== SOME SIGNALS FOR <info>%s - %s</info> ON %s =====</>', $currency, $stock, date('M dS, H:i\h')));
        $this->output->writeln('');
    }

    private function outputSignalsBasedOn(
        string $interval,
        string $interval_unit,
        string $currency,
        string $stock
    ): void {
        $prediction_request = new PredictStockValueRequest($currency, $stock, $interval_unit);
        $prediction_response = $this->stock_predict_service->predict($prediction_request);

        $signals = $this->get_signals_service->getSignals($prediction_response->realMeasurements());

        $this->output->writeln(
            '<options=bold>Signals based on last ' . $interval . ' (Score: ' . CalculateScore::calculate(
                ...
                $signals
            ) . '):</>'
        );

        foreach ($signals as $signal) {
            $this->output->writeln(' - <comment>' . $signal . '</comment>');
        }
        $this->output->writeln('');
    }
}
