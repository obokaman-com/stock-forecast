<?php

namespace Obokaman\StockForecast\Domain\Service\Signal\Extract;


use Obokaman\StockForecast\Domain\Model\Financial\Signal;
use Obokaman\StockForecast\Domain\Service\Signal\GetSignalsFromMeasurements;

class ByPriceChange implements SignalExtract
{
    public function extract(SignalExtractRequest $a_request): ?array
    {
        if ($a_request->long_term_measurements->priceChangePercent() > GetSignalsFromMeasurements::SENSIBILITY_THRESHOLD * 5) {
            return [Signal::EXCELLENT('Earning ' . $a_request->long_term_measurements->priceChangePercent() . '% in this period.')];
        }

        if ($a_request->long_term_measurements->priceChangePercent() > GetSignalsFromMeasurements::SENSIBILITY_THRESHOLD) {
            return [Signal::GOOD('Earning ' . $a_request->long_term_measurements->priceChangePercent() . '% in this period.')];
        }

        if ($a_request->long_term_measurements->priceChangePercent() < -GetSignalsFromMeasurements::SENSIBILITY_THRESHOLD * 5) {
            return [Signal::POOR('Loosing ' . $a_request->long_term_measurements->priceChangePercent() . '% in this period.')];
        }

        if ($a_request->long_term_measurements->priceChangePercent() < -GetSignalsFromMeasurements::SENSIBILITY_THRESHOLD) {
            return [Signal::BAD('Loosing ' . $a_request->long_term_measurements->priceChangePercent() . '% in this period.')];
        }

        return null;
    }
}