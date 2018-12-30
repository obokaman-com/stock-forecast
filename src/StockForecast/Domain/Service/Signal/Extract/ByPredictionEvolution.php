<?php

namespace Obokaman\StockForecast\Domain\Service\Signal\Extract;

use Obokaman\StockForecast\Domain\Model\Financial\Signal;
use Obokaman\StockForecast\Domain\Service\Signal\GetSignalsFromMeasurements;

class ByPredictionEvolution implements SignalExtract
{
    private $request;

    public function extract(SignalExtractRequest $a_request): ?array
    {
        $this->request = $a_request;

        if ($this->isPredictionProgressivelyBetter()) {
            return [Signal::GOOD('Better predictions on short term measurements.')];
        }

        if ($this->isPredictionProgressivelyWorse()) {
            return [Signal::BAD('Worse predictions on short term measurements.')];
        }

        return null;
    }

    private function isPredictionProgressivelyBetter(): bool
    {
        return $this->request->prediction_percentage_on_medium > $this->request->prediction_percentage_on_long && $this->request->prediction_percentage_on_short > $this->request->prediction_percentage_on_medium && $this->request->diff_change_percent > GetSignalsFromMeasurements::SENSIBILITY_THRESHOLD;
    }

    private function isPredictionProgressivelyWorse(): bool
    {
        return $this->request->prediction_percentage_on_medium < $this->request->prediction_percentage_on_long && $this->request->prediction_percentage_on_short < $this->request->prediction_percentage_on_medium && $this->request->diff_change_percent > GetSignalsFromMeasurements::SENSIBILITY_THRESHOLD;
    }
}