<?php

namespace Obokaman\StockForecast\Domain\Service\Signal;

use Obokaman\StockForecast\Domain\Model\Date\Period;
use Obokaman\StockForecast\Domain\Model\Financial\Signal;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\MeasurementCollection;
use Obokaman\StockForecast\Domain\Service\Predict\Predict;
use Obokaman\StockForecast\Domain\Service\Signal\Extract\SignalExtract;
use Obokaman\StockForecast\Domain\Service\Signal\Extract\SignalExtractRequest;

final class GetSignalsFromMeasurements
{
    public const SENSIBILITY_THRESHOLD = 1;
    public const NOTICEABLE_CHANGES    = 5;
    public const VOLATILITY_THRESHOLD  = 75;

    private $prediction_service;

    /** @var Signal[] */
    private $signals;

    /** @var SignalExtract[] */
    private $extract_services;

    public function __construct(Predict $a_prediction_service, SignalExtract ... $extract_services)
    {
        $this->prediction_service = $a_prediction_service;
        $this->extract_services   = $extract_services;
    }

    /**
     * @param MeasurementCollection $measurements
     *
     * @return Signal[]
     */
    public function getSignals(MeasurementCollection $measurements): array
    {
        $this->signals             = [];
        $signal_extraction_request = $this->buildExtractRequest($measurements);

        foreach ($this->extract_services as $extract_service) {
            $this->addSignals($extract_service->extract($signal_extraction_request));
        }

        return $this->signals ?: [Signal::NEUTRAL('No relevant signals.')];
    }

    private function buildExtractRequest(MeasurementCollection $measurements): SignalExtractRequest
    {
        return new SignalExtractRequest(
            $measurements,
            $measurements->filterByPeriod(Period::MEDIUM),
            $measurements->filterByPeriod(Period::SHORT),

            $measurements->priceChangePercent(),
            $measurements->filterByPeriod(Period::MEDIUM)->priceChangePercent(),
            $measurements->filterByPeriod(Period::SHORT)->priceChangePercent(),

            $this->prediction_service->predict($measurements)->priceChangePercent(),
            $this->prediction_service->predict($measurements->filterByPeriod(Period::MEDIUM))->priceChangePercent(),
            $this->prediction_service->predict($measurements->filterByPeriod(Period::SHORT))->priceChangePercent()
        );
    }

    private function addSignals(?array $signals): void
    {
        if (empty($signals)) {
            return;
        }

        $this->signals = array_merge($this->signals, $signals);
    }
}
