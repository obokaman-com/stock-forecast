<?php

namespace App\Command;

use Obokaman\StockForecast\Domain\Service\Predict\PredictionStrategy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Test extends Command
{
    private const SAMPLE_DATA = [1, 3, 5, 7, 9, 11];
    private $prediction_service;

    public function __construct(PredictionStrategy $a_prediction_strategy)
    {
        $this->prediction_service = $a_prediction_strategy;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('forecast:test')
             ->setDescription('Predict a stock future value.')
             ->setHelp('This command allow you to predict a stock future value...')
             ->addArgument('sequence', InputArgument::IS_ARRAY, '', self::SAMPLE_DATA);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sequence = $input->getArgument('sequence');

        $this->prediction_service->train($sequence);

        $result = $this->prediction_service->predictNext(\count($sequence));

        $output->writeln('Sample data: ' . implode(',', $sequence));
        $output->writeln('Next items: ' . implode(',', $result));
    }

}
