<?php

namespace Obokaman\StockForecast\Domain\Service\Signal\Extract;

use Obokaman\StockForecast\Domain\Model\Financial\Signal;
use Obokaman\StockForecast\Domain\Service\Signal\GetSignalsFromMeasurements;

class ByConsistency implements SignalExtract
{
    private $request;

    public function extract(SignalExtractRequest $a_request): ?array
    {
        $this->request = $a_request;

        if ($this->isAllPositive()) {
            return [Signal::EXCELLENT('Earning in all date ranges.')];
        }

        if ($this->isAllNegative()) {
            return [Signal::POOR('Loosing in all date ranges.')];
        }

        return null;
    }

    private function isAllPositive(): bool
    {
        return $this->request->change_percentage_on_long > GetSignalsFromMeasurements::SENSIBILITY_THRESHOLD && $this->request->change_percentage_on_medium > GetSignalsFromMeasurements::SENSIBILITY_THRESHOLD && $this->request->change_percentage_on_short > GetSignalsFromMeasurements::SENSIBILITY_THRESHOLD;
    }

    private function isAllNegative(): bool
    {
        return $this->request->change_percentage_on_long < -GetSignalsFromMeasurements::SENSIBILITY_THRESHOLD && $this->request->change_percentage_on_medium < -GetSignalsFromMeasurements::SENSIBILITY_THRESHOLD && $this->request->change_percentage_on_short < -GetSignalsFromMeasurements::SENSIBILITY_THRESHOLD;
    }
}