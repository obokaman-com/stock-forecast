<?php

namespace App\Command;

use Obokaman\StockForecast\Application\Service\PredictStockValue;
use Obokaman\StockForecast\Application\Service\PredictStockValueRequest;
use Obokaman\StockForecast\Domain\Model\Date\Interval;
use Obokaman\StockForecast\Domain\Model\Date\Period;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\Measurement;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\MeasurementCollection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Predict extends Command
{
    private $stock_predict_service;

    public function __construct(PredictStockValue $a_stock_predict_service)
    {
        $this->stock_predict_service = $a_stock_predict_service;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('forecast:stock')
             ->setDescription('Generates a forecast of stock future prices.')
             ->setHelp('This command allow you to predict a stock future value...')
             ->addArgument('currency', InputArgument::OPTIONAL, 'The currency code.', 'USD')
             ->addArgument('stock', InputArgument::OPTIONAL, 'The stock code.', 'BTC')
             ->addArgument('date_interval', InputArgument::OPTIONAL, 'The date interval to use in the forecast (minutes, hours, days).', 'days');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->outputCommandTitle($input, $output);

        $prediction_request = new PredictStockValueRequest($input->getArgument('currency'), $input->getArgument('stock'), $input->getArgument('date_interval'));

        $prediction_response = $this->stock_predict_service->predict($prediction_request);

        $output->writeln('<options=bold>Last real measurements:</>');
        $this->outputStatsTable($output, $prediction_response->realMeasurements());

        $output->writeln('<options=bold>Changes in last days ' . $input->getArgument('date_interval') . ':</>');
        $this->outputChangeTable($output, $prediction_response->realMeasurements());

        $output->writeln('<options=bold>Forecast for next ' . $input->getArgument('date_interval') . ':</>');
        $this->outputForecastTable(
            $output,
            $prediction_response->shortTermPredictions(),
            $prediction_response->mediumTermPredictions(),
            $prediction_response->longTermPredictions()
        );

        return 0;
    }

    private function outputCommandTitle(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln(
            sprintf(
                '<options=bold>===== BUILDING FORECAST FOR <info>%s - %s</info> USING DATA FROM LAST %d %s =====</>',
                $input->getArgument('stock'),
                $input->getArgument('currency'),
                Period::getLong(Interval::fromStringDateInterval($input->getArgument('date_interval'))),
                $input->getArgument('date_interval')
            )
        );
        $output->writeln('');
    }

    private function outputStatsTable(OutputInterface $output, MeasurementCollection $measurements): void
    {
        $table = new Table($output);
        $table->setHeaders(['Date', 'Open', 'Close', 'Change', 'Change (%)', 'High', 'Low', 'Volatility', 'Volume']);

        foreach ($measurements as $stock_stats) {
            $table->addRow(
                [
                    $stock_stats->timestamp()->format('Y-m-d H:i'),
                    $stock_stats->open(),
                    $stock_stats->close(),
                    $stock_stats->change(),
                    $stock_stats->changePercent() . '%',
                    $stock_stats->high(),
                    $stock_stats->low(),
                    $stock_stats->volatility(),
                    $stock_stats->volume()
                ]
            );
        }
        $table->render();
        $output->writeln('');
    }

    private function outputForecastTable(OutputInterface $output, MeasurementCollection ...$stock_predictions): void
    {
        $table = new Table($output);
        $table->setHeaders(
            [
                'Date interval',
                'Open',
                'Close',
                'Change',
                'Change (%)',
                'High',
                'Low',
                'Volatility',
                'Volume'
            ]
        );

        [$short_term_predictions, $mid_term_predictions, $long_term_predictions] = $stock_predictions;

        $this->addStocksTableRow($table, 'Short term', $short_term_predictions->current());
        $this->addStocksTableRow($table, 'Medium term', $mid_term_predictions->current());
        $this->addStocksTableRow($table, 'Long term', $long_term_predictions->current());

        $table->render();
        $output->writeln('');
    }

    private function addStocksTableRow(Table $table, string $label, Measurement $stats): void
    {
        $table->addRow(
            [
                $label,
                $stats->open(),
                $stats->close(),
                $stats->change(),
                $stats->changePercent() . '%',
                $stats->high(),
                $stats->low(),
                $stats->volatility(),
                $stats->volume()
            ]
        );
    }

    private function outputChangeTable(OutputInterface $output, MeasurementCollection $long_term_measurements): void
    {
        $table = new Table($output);
        $table->setHeaders(['Date interval', 'Aggregated Change', 'Aggregated Change (%)']);

        $short_term_measurements = $long_term_measurements->filterByPeriod(Period::SHORT);
        $table->addRow(
            [
                'Short term',
                $short_term_measurements->priceChangeAmount(),
                $short_term_measurements->priceChangePercent() . '%'
            ]
        );

        $mid_term_measurements = $long_term_measurements->filterByPeriod(Period::MEDIUM);
        $table->addRow(
            [
                'Medium term',
                $mid_term_measurements->priceChangeAmount(),
                $mid_term_measurements->priceChangePercent() . '%'
            ]
        );

        $table->addRow(
            [
                'Long term',
                $long_term_measurements->priceChangeAmount(),
                $long_term_measurements->priceChangePercent() . '%'
            ]
        );

        $table->render();
        $output->writeln('');
    }
}
