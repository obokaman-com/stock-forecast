<?php

namespace Obokaman\StockForecast\Domain\Service\Signal\Extract;

use Obokaman\StockForecast\Domain\Model\Financial\Signal;
use Obokaman\StockForecast\Domain\Service\Signal\GetSignalsFromMeasurements;

class ByShortTermChanges implements SignalExtract
{
    private $request;

    public function extract(SignalExtractRequest $a_request): ?array
    {
        $this->request = $a_request;

        $signals = [];

        if ($this->isRecoveringOnShort()) {
            $signals[] = Signal::GOOD('Recovering value in short term (' . $this->request->change_percentage_on_short . '%) after being in down tendency.');
        }

        if ($this->isLoosingOnShort()) {
            $signals[] = Signal::BAD('Loosing value in short term (' . $this->request->change_percentage_on_short . '%) after being in up tendency.');
        }

        if ($this->isNoticeableRecentImprovement()) {
            $signals[] = Signal::GOOD('Having a noticeable improvement recently (' . $this->request->change_percentage_on_short . '%).');
        }

        if ($this->isNoticeableRecentDecrease()) {
            $signals[] = Signal::BAD('Having a noticeable decrease recently (' . $this->request->change_percentage_on_short . '%).');
        }

        return !empty($signals) ? $signals : null;
    }

    private function isRecoveringOnShort(): bool
    {
        return $this->request->change_percentage_on_short > GetSignalsFromMeasurements::SENSIBILITY_THRESHOLD && $this->request->change_percentage_on_medium < 0 && $this->request->change_percentage_on_long < 0;
    }

    private function isLoosingOnShort(): bool
    {
        return $this->request->change_percentage_on_short < -GetSignalsFromMeasurements::SENSIBILITY_THRESHOLD && $this->request->change_percentage_on_medium > 0 && $this->request->change_percentage_on_long > 0;
    }

    private function isNoticeableRecentImprovement(): bool
    {
        return $this->request->change_percentage_on_short >= GetSignalsFromMeasurements::NOTICEABLE_CHANGES;
    }

    private function isNoticeableRecentDecrease(): bool
    {
        return $this->request->change_percentage_on_short <= -GetSignalsFromMeasurements::NOTICEABLE_CHANGES;
    }
}