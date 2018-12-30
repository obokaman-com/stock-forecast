<?php

namespace Obokaman\StockForecast\Domain\Service\Signal\Extract;

use Obokaman\StockForecast\Domain\Model\Financial\Signal;
use Obokaman\StockForecast\Domain\Service\Signal\GetSignalsFromMeasurements;

class ByVolatility implements SignalExtract
{
    private $request;

    public function extract(SignalExtractRequest $a_request): ?array
    {
        $this->request = $a_request;

        if ($this->hasLowVolatility()) {
            return [Signal::NEUTRAL('Low volatility in this period (' . $this->request->max_change_percent . '%).')];
        }

        if ($this->hasHighVolatility()) {
            return [Signal::NEUTRAL('High volatility during this period: Max increase: ' . $this->request->max_change_amount . '%, Max. decrease: ' . $this->request->min_change_amount . '%.')];
        }

        return null;
    }

    private function hasLowVolatility(): bool
    {
        return $this->request->diff_change_percent < GetSignalsFromMeasurements::SENSIBILITY_THRESHOLD;
    }

    private function hasHighVolatility(): bool
    {
        return $this->request->diff_change_percent >= GetSignalsFromMeasurements::VOLATILITY_THRESHOLD;
    }
}