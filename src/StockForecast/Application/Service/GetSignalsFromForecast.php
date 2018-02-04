<?php

namespace Obokaman\StockForecast\Application\Service;

use Obokaman\StockForecast\Domain\Model\Date\Period;
use Obokaman\StockForecast\Domain\Model\Financial\Signal;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\MeasurementCollection;

final class GetSignalsFromForecast
{
    private const SENSIBILITY_THRESHOLD = 0.75;
    private const NOTICEABLE_CHANGES = 5;
    private const VOLATILITY_THRESHOLD = 75;

    /** @var Signal[] */
    private $signals;

    /** @var MeasurementCollection */
    private $long_term_measurements;
    /** @var MeasurementCollection */
    private $mid_term_measurements;
    /** @var MeasurementCollection */
    private $short_term_measurements;

    private $change_on_long;
    private $change_on_medium;
    private $change_on_short;
    private $max_change;
    private $min_change;
    private $diff_change;

    private $regression_on_short;
    private $regression_on_medium;
    private $regression_on_long;

    /**
     * @param GetSignalsFromForecastRequest $a_get_signals_request
     *
     * @return Signal[]
     */
    public function getSignals(GetSignalsFromForecastRequest $a_get_signals_request): array
    {
        $this->long_term_measurements  = $a_get_signals_request->measurements();
        $this->mid_term_measurements   = $a_get_signals_request->measurements()->filterByPeriod(Period::MEDIUM);
        $this->short_term_measurements = $a_get_signals_request->measurements()->filterByPeriod(Period::SHORT);

        $this->signals = [];

        $this->change_on_long   = $this->long_term_measurements->priceChangePercent();
        $this->change_on_medium = $this->mid_term_measurements->priceChangePercent();
        $this->change_on_short  = $this->short_term_measurements->priceChangePercent();

        $this->regression_on_short  = $a_get_signals_request
            ->shortTermPredictions()
            ->filterByQuantity(2)
            ->priceChangePercent();
        $this->regression_on_medium = $a_get_signals_request
            ->mediumTermPredictions()
            ->filterByQuantity(2)
            ->priceChangePercent();
        $this->regression_on_long   = $a_get_signals_request
            ->longTermPredictions()
            ->filterByQuantity(2)
            ->priceChangePercent();

        $this->setMaxMinAndDiff();

        if ($this->long_term_measurements->priceChangePercent() > self::SENSIBILITY_THRESHOLD)
        {
            $this->addSignal(Signal::GOOD('Earning ' . $this->long_term_measurements->priceChangePercent() . '% in this period.'));
        }

        if ($this->long_term_measurements->priceChangePercent() < -self::SENSIBILITY_THRESHOLD)
        {
            $this->addSignal(Signal::BAD('Loosing ' . $this->long_term_measurements->priceChangePercent() . '% in this period.'));
        }

        if ($this->isPositiveRegression())
        {
            $this->addSignal(Signal::GOOD('Positive tendency in this period (' . $this->regression_on_long . '%).'));
        }

        if ($this->isNegativeRegression())
        {
            $this->addSignal(Signal::BAD('Negative tendency in this period (' . $this->regression_on_long . '%).'));
        }

        if ($this->isStable())
        {
            $this->addSignal(Signal::NEUTRAL('Stable in this period.'));
        }

        if ($this->isRegressionExponentialUp())
        {
            $this->addSignal(Signal::GOOD('Improving exponentially.'));
        }

        if ($this->isRegressionExponentialDown())
        {
            $this->addSignal(Signal::BAD('Deteriorating exponentially.'));
        }

        if ($this->isAllPositive())
        {
            $this->addSignal(Signal::EXCELLENT('Earning in all date ranges.'));
        }

        if ($this->isAllNegative())
        {
            $this->addSignal(Signal::POOR('Loosing in all date ranges.'));
        }

        if ($this->isRecovering())
        {
            $this->addSignal(Signal::GOOD('Recovering value in short term.'));
        }

        if ($this->isLoosing())
        {
            $this->addSignal(Signal::BAD('Loosing value in short term.'));
        }

        if ($this->isNoticeableRecentImprovement())
        {
            $this->addSignal(Signal::GOOD('Having a noticeable improvement recently (' . $this->change_on_short . '%).'));
        }

        if ($this->isNoticeableRecentDecrease())
        {
            $this->addSignal(Signal::BAD('Having a noticeable decrease recently (' . $this->change_on_short . '%).'));
        }

        if ($this->isHighlyVolatile())
        {
            $this->addSignal(Signal::NEUTRAL('High volatility during this period: Max increase: ' . $this->max_change . '%, Max. decrease: ' . $this->min_change . '%.'));
        }

        return $this->getAllSignals();
    }

    private function isStable(): bool
    {
        return $this->diff_change < self::SENSIBILITY_THRESHOLD;
    }

    private function isRegressionExponentialUp(): bool
    {
        return $this->regression_on_medium > $this->regression_on_long && $this->regression_on_short > $this->regression_on_medium && $this->diff_change > self::SENSIBILITY_THRESHOLD;
    }

    private function isRegressionExponentialDown(): bool
    {
        return $this->regression_on_medium < $this->regression_on_long && $this->regression_on_short < $this->regression_on_medium && $this->diff_change > self::SENSIBILITY_THRESHOLD;
    }

    private function isAllPositive(): bool
    {
        return $this->change_on_long > 0 && $this->change_on_medium > 0 && $this->change_on_short > 0 && $this->max_change > self::SENSIBILITY_THRESHOLD;
    }

    private function isAllNegative(): bool
    {
        return $this->change_on_long < 0 && $this->change_on_medium < 0 && $this->change_on_short < 0 && $this->min_change < -self::SENSIBILITY_THRESHOLD;
    }

    private function isRecovering(): bool
    {
        return ($this->change_on_short > self::SENSIBILITY_THRESHOLD
            && ($this->change_on_medium < 0 && $this->change_on_long < 0));
    }

    private function isLoosing(): bool
    {
        return ($this->change_on_short < -self::SENSIBILITY_THRESHOLD
            && ($this->change_on_medium > 0 && $this->change_on_long > 0));
    }

    private function isNoticeableRecentImprovement(): bool
    {
        return $this->change_on_short >= self::NOTICEABLE_CHANGES;
    }

    private function isNoticeableRecentDecrease(): bool
    {
        return $this->change_on_short <= -self::NOTICEABLE_CHANGES;
    }

    private function isHighlyVolatile(): bool
    {
        return $this->diff_change >= self::VOLATILITY_THRESHOLD;
    }

    private function addSignal(Signal $signal): void
    {
        $this->signals[] = $signal;
    }

    private function setMaxMinAndDiff(): void
    {
        $this->max_change = 0;
        $this->min_change = 0;

        /** @var \Obokaman\StockForecast\Domain\Model\Financial\Stock\Measurement $stock_stats */
        foreach ($this->long_term_measurements as $stock_stats)
        {
            $change_percent = $stock_stats->changePercent();
            if ($change_percent > $this->max_change)
            {
                $this->max_change = $change_percent;
            }
            if ($change_percent < $this->min_change)
            {
                $this->min_change = $change_percent;
            }
        }

        $this->diff_change = $this->max_change - $this->min_change;
    }

    /** @return Signal[] */
    private function getAllSignals(): array
    {
        return $this->signals ?: [Signal::NEUTRAL('No relevant signals.')];
    }

    private function isPositiveRegression(): bool
    {
        return $this->regression_on_long > self::SENSIBILITY_THRESHOLD;
    }

    private function isNegativeRegression(): bool
    {
        return $this->regression_on_long < -self::SENSIBILITY_THRESHOLD;
    }
}
