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
            ->setHelp(
                'This command allow you to predict a stock future value...'
            )
            ->addArgument('currency', InputArgument::OPTIONAL, 'The currency code.', 'USD')
            ->addArgument('stock', InputArgument::OPTIONAL, 'The stock code.', 'BTC')
            ->addArgument('date_interval', InputArgument::OPTIONAL, 'The date interval to use in the forecast (minutes, hours, days).', 'days');
    }

    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $this->outputCommandTitle($input, $output);

        $prediction_request = new PredictStockValueRequest(
            $input->getArgument('currency'),
            $input->getArgument('stock'),
            $input->getArgument('date_interval')
        );

        $prediction_response = $this->stock_predict_service->predict($prediction_request);

        $output->writeln('<options=bold>Last real measurements:</>');
        $this->outputStatsTable($output, $prediction_response->realStatsArray());

        $output->writeln('<options=bold>Forecast for next ' . $input->getArgument('date_interval') . ':</>');
        $this->outputForecastTable(
            $output,
            $prediction_response->shortTermStats(),
            $prediction_response->mediumTermStats(),
            $prediction_response->longTermStats()
        );
    }

    private function outputCommandTitle(InputInterface $input, OutputInterface $output): void
    {
        $output->writeln(
            sprintf(
                '<options=bold>===== BUILDING FORECAST FOR <info>%s - %s</info> USING DATA FROM LAST 30 %s =====</>',
                $input->getArgument('stock'),
                $input->getArgument('currency'),
                $input->getArgument('date_interval')
            )
        );
        $output->writeln('');
    }

    private function outputStatsTable(OutputInterface $output, array $measurements): void
    {
        $table = new Table($output);
        $table->setHeaders(['Date', 'Open', 'Close', 'Change', 'Change (%)', 'High', 'Low', 'Volatility', 'Volume']);

        /** @var StockStats $stock_stats */
        foreach ($measurements as $stock_stats)
        {
            $table->addRow(
                [
                    $stock_stats->timestamp()->format('Y-m-d H:i'),
                    $stock_stats->open(),
                    $stock_stats->close(),
                    $stock_stats->change(),
                    $stock_stats->changePercent(),
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

    private function outputForecastTable(OutputInterface $output, StockStats ...$stock_stats)
    {
        $table = new Table($output);
        $table->setHeaders(['Date interval', 'Open', 'Close', 'Change', 'Change (%)', 'High', 'Low', 'Volatility', 'Volume']);

        $this->addTableRow($table, 'Short term', $stock_stats[0]);
        $this->addTableRow($table, 'Medium term', $stock_stats[1]);
        $this->addTableRow($table, 'Long term', $stock_stats[2]);

        $table->render();
        $output->writeln('');
    }

    private function addTableRow(Table $table, string $label, StockStats $stats): void
    {
        $table->addRow(
            [
                $label,
                $stats->open(),
                $stats->close(),
                $stats->change(),
                $stats->changePercent(),
                $stats->high(),
                $stats->low(),
                $stats->volatility(),
                $stats->volume()
            ]
        );
    }
}
