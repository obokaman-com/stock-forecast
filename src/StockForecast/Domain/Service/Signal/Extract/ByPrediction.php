<?php

namespace Obokaman\StockForecast\Domain\Service\Signal\Extract;

use Obokaman\StockForecast\Domain\Model\Financial\Signal;
use Obokaman\StockForecast\Domain\Service\Signal\GetSignalsFromMeasurements;

class ByPrediction implements SignalExtract
{
    private $request;

    public function extract(SignalExtractRequest $a_request): ?array
    {
        $this->request = $a_request;

        if ($this->hasVeryPositivePrediction()) {
            return [Signal::EXCELLENT('Positive prediction based in this period (' . $this->request->prediction_percentage_on_long . '%).')];
        }

        if ($this->hasPositivePrediction()) {
            return [Signal::GOOD('Positive prediction based in this period (' . $this->request->prediction_percentage_on_long . '%).')];
        }

        if ($this->hasVeryNegativePrediction()) {
            return [Signal::POOR('Negative prediction based in this period (' . $this->request->prediction_percentage_on_long . '%).')];
        }

        if ($this->hasNegativePrediction()) {
            return [Signal::BAD('Negative prediction based in this period (' . $this->request->prediction_percentage_on_long . '%).')];
        }

        return null;
    }

    private function hasPositivePrediction(): bool
    {
        return $this->request->prediction_percentage_on_long > GetSignalsFromMeasurements::SENSIBILITY_THRESHOLD;
    }

    private function hasVeryPositivePrediction(): bool
    {
        return $this->request->prediction_percentage_on_long > GetSignalsFromMeasurements::SENSIBILITY_THRESHOLD * 5;
    }

    private function hasNegativePrediction(): bool
    {
        return $this->request->prediction_percentage_on_long < -GetSignalsFromMeasurements::SENSIBILITY_THRESHOLD;
    }

    private function hasVeryNegativePrediction(): bool
    {
        return $this->request->prediction_percentage_on_long < -GetSignalsFromMeasurements::SENSIBILITY_THRESHOLD * 5;
    }
}