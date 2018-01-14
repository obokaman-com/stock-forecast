<?php

namespace Obokaman\StockForecast\Application\Service;

final class GetSignalsFromForecast
{
    private const SENSIBILITY_THRESHOLD = 0.75;

    private $signals;
    private $change_on_long;
    private $change_on_medium;
    private $change_on_short;
    private $max_change;
    private $min_change;
    private $diff_change;

    public function getSignals(GetSignalsFromForecastRequest $a_get_signals_request): array
    {
        $this->signals = [];

        $this->change_on_long   = $a_get_signals_request->longTermStats()->changePercent();
        $this->change_on_medium = $a_get_signals_request->mediumTermStats()->changePercent();
        $this->change_on_short  = $a_get_signals_request->shortTermStats()->changePercent();

        $this->setMaxMinAndDiff();

        if ($this->isStable())
        {
            $this->addSignal('Stable in this period.');
        }

        if ($this->isExponentialUp())
        {
            $this->addSignal('Improve exponentially.');
        }

        if ($this->isExponentialDown())
        {
            $this->addSignal('Deteriorate exponentially.');
        }

        if ($this->isAllPositive())
        {
            $this->addSignal('Earning in all scenarios.');
        }

        if ($this->isAllNegative())
        {
            $this->addSignal('Loosing in all scenarios.');
        }

        if ($this->isRecovering())
        {
            $this->addSignal('Recovering value in short term');
        }

        if ($this->isLoosing())
        {
            $this->addSignal('Loosing value in short term');
        }

        return $this->getAllSignals();
    }

    private function isStable(): bool
    {
        return $this->diff_change < self::SENSIBILITY_THRESHOLD;
    }

    private function isAllPositive(): bool
    {
        return $this->change_on_long > 0 && $this->change_on_medium > 0 && $this->change_on_short > 0 && $this->max_change > self::SENSIBILITY_THRESHOLD;
    }

    private function isAllNegative(): bool
    {
        return $this->change_on_long < 0 && $this->change_on_medium < 0 && $this->change_on_short < 0 && $this->min_change < -self::SENSIBILITY_THRESHOLD;
    }

    private function isExponentialUp(): bool
    {
        return $this->change_on_medium > $this->change_on_long && $this->change_on_short > $this->change_on_medium && $this->diff_change > self::SENSIBILITY_THRESHOLD;
    }

    private function isExponentialDown(): bool
    {
        return $this->change_on_medium < $this->change_on_long && $this->change_on_short < $this->change_on_medium && $this->diff_change > self::SENSIBILITY_THRESHOLD;
    }

    private function isRecovering(): bool
    {
        return ($this->change_on_short > self::SENSIBILITY_THRESHOLD
            && ($this->change_on_medium < 0 || $this->change_on_long < 0));
    }

    private function isLoosing(): bool
    {
        return ($this->change_on_short < -self::SENSIBILITY_THRESHOLD
            && ($this->change_on_medium > 0 || $this->change_on_long > 0));
    }

    private function setMaxMinAndDiff(): void
    {
        $this->max_change = $this->change_on_long;
        if ($this->change_on_medium > $this->max_change)
        {
            $this->max_change = $this->change_on_medium;
        }
        if ($this->change_on_short > $this->max_change)
        {
            $this->max_change = $this->change_on_short;
        }

        $this->min_change = $this->change_on_long;
        if ($this->change_on_medium < $this->min_change)
        {
            $this->min_change = $this->change_on_medium;
        }
        if ($this->change_on_short < $this->min_change)
        {
            $this->min_change = $this->change_on_short;
        }

        $this->diff_change = $this->max_change - $this->min_change;
    }

    private function addSignal(string $signal): void
    {
        $this->signals[] = $signal;
    }

    private function getAllSignals()
    {
        return $this->signals ?: ['No relevant signals'];
    }
}