<?php

namespace Obokaman\StockForecast\Domain\Service\Predict;

interface PredictionStrategy
{
    public function train(array $data_sequence): void;

    public function resetTraining();

    public function predictNext(int $quantity = 1);
}
