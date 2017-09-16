<?php

namespace App\Command;

use Obokaman\StockForecast\Application\Service\PredictStockValueRequest;
use Obokaman\StockForecast\Domain\Model\Financial\StockStats;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Predict extends ContainerAwareCommand
{
    private $stock_predict_service;

    protected function configure()
    {
        $this->setName('stocks:predict')
            ->setDescription('Predict a stock future value.')
            ->setHelp('This command allow you to predict a stock future value...')
            ->addArgument('currency', InputArgument::OPTIONAL, 'The currency code.', 'USD')
            ->addArgument('stock', InputArgument::OPTIONAL, 'The stock code.', 'BTC')
            ->addArgument('days_to_collect', InputArgument::OPTIONAL, 'The days to recover from stats.', '10')
            ->addArgument('days_to_forecast', InputArgument::OPTIONAL, 'The days to predict in the forecast.', '3');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->stock_predict_service = $this->getContainer()->get('obokaman.stock.forecast.predict');

        $this->outputCommandTitle($input, $output);

        $prediction_response = $this->stock_predict_service->predict(
            new PredictStockValueRequest(
                $input->getArgument('currency'), $input->getArgument('stock'), $input->getArgument('days_to_collect'), $input->getArgument('days_to_forecast')
            )
        );

        $output->writeln('<options=bold>Last real measurements:</>');
        $this->outputMeasurementsTable($output, $prediction_response->lastDaysRealStatsArray());

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
        $table->setHeaders(['Date', 'Open', 'Close', 'High', 'Low', 'Volume from', 'Volume to']);

        /** @var StockStats $stock_stats */
        foreach ($measurements as $stock_stats)
        {
            $table->addRow(
                [
                    $stock_stats->timestamp()->format('Y-m-d'),
                    $stock_stats->open(),
                    $stock_stats->close(),
                    $stock_stats->high(),
                    $stock_stats->low(),
                    $stock_stats->volumeFrom(),
                    $stock_stats->volumeTo()
                ]
            );
        }
        $table->render();
        $output->writeln('');
    }
}
