<?php

namespace App\Command;

use Obokaman\StockForecast\Application\Service\PredictStockValue;
use Obokaman\StockForecast\Application\Service\PredictStockValueRequest;
use Obokaman\StockForecast\Domain\Model\Financial\StockStats;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Predict extends Command
{
    private const MAX_REAL_STATS_QUANTITY_OUTPUT = 3;

    private $stock_predict_service;

    public function __construct(PredictStockValue $a_stock_predict_service)
    {
        $this->stock_predict_service = $a_stock_predict_service;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('forecast:stock')
            ->setDescription('Generates a forecast of stock future prices.')
            ->setHelp(
                'This command allow you to predict a stock future value...'
            )
            ->addArgument('currency', InputArgument::OPTIONAL, 'The currency code.', 'USD')
            ->addArgument('stock', InputArgument::OPTIONAL, 'The stock code.', 'BTC')
            ->addArgument('days_to_collect', InputArgument::OPTIONAL, 'The days to recover from stats.', '10')
            ->addArgument('days_to_forecast', InputArgument::OPTIONAL, 'The days to predict in the forecast.', '3');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->outputCommandTitle($input, $output);

        $prediction_response = $this->stock_predict_service->predict(
            new PredictStockValueRequest(
                $input->getArgument('currency'), $input->getArgument('stock'), $input->getArgument('days_to_collect'), $input->getArgument('days_to_forecast')
            )
        );

        $output->writeln('<options=bold>Last real measurements:</>');
        $this->outputMeasurementsTable(
            $output,
            array_slice($prediction_response->realStatsArray(), -self::MAX_REAL_STATS_QUANTITY_OUTPUT, self::MAX_REAL_STATS_QUANTITY_OUTPUT)
        );

        $output->writeln('<options=bold>Forecast for next few days:</>');
        $this->outputMeasurementsTable($output, $prediction_response->forecastStatsArray());
    }

    private function outputCommandTitle(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln(
            sprintf(
                '<options=bold>===== BUILDING FORECAST FOR <info>%s - %s</info> USING DATA FROM LAST %d DAYS =====</>',
                $input->getArgument('stock'),
                $input->getArgument('currency'),
                $input->getArgument('days_to_collect')
            )
        );
        $output->writeln('');
    }

    private function outputMeasurementsTable(OutputInterface $output, array $measurements): void
    {
        $table = new Table($output);
        $table->setHeaders(['Date', 'Open', 'Close', 'Change', 'High', 'Low', 'Volatility', 'Volume']);

        /** @var StockStats $stock_stats */
        foreach ($measurements as $stock_stats)
        {
            $table->addRow(
                [
                    $stock_stats->timestamp()->format('Y-m-d'),
                    $stock_stats->open(),
                    $stock_stats->close(),
                    $stock_stats->change(),
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
}
