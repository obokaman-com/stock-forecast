<?php

namespace Obokaman\StockForecast\Domain\Service\Signal;

use Obokaman\StockForecast\Domain\Model\Date\Period;
use Obokaman\StockForecast\Domain\Model\Financial\Signal;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\MeasurementCollection;
use Obokaman\StockForecast\Domain\Service\Predict\Predict;

final class GetSignalsFromMeasurements
{
    private const SENSIBILITY_THRESHOLD = 1;
    private const NOTICEABLE_CHANGES = 5;
    private const VOLATILITY_THRESHOLD = 75;

    private $prediction_service;

    /** @var Signal[] */
    private $signals;

    /** @var MeasurementCollection */
    private $long_term_measurements;
    /** @var MeasurementCollection */
    private $mid_term_measurements;
    /** @var MeasurementCollection */
    private $short_term_measurements;

    private $change_percentage_on_long;
    private $change_percentage_on_medium;
    private $change_percentage_on_short;

    private $prediction_percentage_on_long;
    private $prediction_percentage_on_medium;
    private $prediction_percentage_on_short;

    private $max_change_amount;
    private $min_change_amount;
    private $diff_change_amount;
    private $max_change_percent;
    private $min_change_percent;
    private $diff_change_percent;

    public function __construct(Predict $a_prediction_service)
    {
        $this->prediction_service = $a_prediction_service;
    }

    /**
     * @param MeasurementCollection $measurements
     *
     * @return Signal[]
     */
    public function getSignals(MeasurementCollection $measurements): array
    {
        $this->signals                     = [];
        $this->long_term_measurements      = $measurements;
        $this->mid_term_measurements       = $this->long_term_measurements->filterByPeriod(Period::MEDIUM);
        $this->short_term_measurements     = $this->long_term_measurements->filterByPeriod(Period::SHORT);
        $this->change_percentage_on_long   = $this->long_term_measurements->priceChangePercent();
        $this->change_percentage_on_medium = $this->mid_term_measurements->priceChangePercent();
        $this->change_percentage_on_short  = $this->short_term_measurements->priceChangePercent();

        $this->prediction_percentage_on_short = $this->prediction_service
            ->predict($this->short_term_measurements)
            ->priceChangePercent();

        $this->prediction_percentage_on_medium = $this->prediction_service
            ->predict($this->mid_term_measurements)
            ->priceChangePercent();

        $this->prediction_percentage_on_long = $this->prediction_service
            ->predict($this->long_term_measurements)
            ->priceChangePercent();

        $this->setMaxMinAndDiff();

        if ($this->long_term_measurements->priceChangePercent() > self::SENSIBILITY_THRESHOLD * 5)
        {
            $this->addSignal(Signal::EXCELLENT('Earning ' . $this->long_term_measurements->priceChangePercent() . '% in this period.'));
        }
        else if ($this->long_term_measurements->priceChangePercent() > self::SENSIBILITY_THRESHOLD)
        {
            $this->addSignal(Signal::GOOD('Earning ' . $this->long_term_measurements->priceChangePercent() . '% in this period.'));
        }

        if ($this->long_term_measurements->priceChangePercent() < -self::SENSIBILITY_THRESHOLD * 5)
        {
            $this->addSignal(Signal::POOR('Loosing ' . $this->long_term_measurements->priceChangePercent() . '% in this period.'));
        }
        else if ($this->long_term_measurements->priceChangePercent() < -self::SENSIBILITY_THRESHOLD)
        {
            $this->addSignal(Signal::BAD('Loosing ' . $this->long_term_measurements->priceChangePercent() . '% in this period.'));
        }

        if ($this->hasVeryPositivePrediction())
        {
            $this->addSignal(Signal::EXCELLENT('Positive prediction based in this period (' . $this->prediction_percentage_on_long . '%).'));
        }
        else if ($this->hasPositivePrediction())
        {
            $this->addSignal(Signal::GOOD('Positive prediction based in this period (' . $this->prediction_percentage_on_long . '%).'));
        }

        if ($this->hasVeryNegativePrediction())
        {
            $this->addSignal(Signal::POOR('Negative prediction based in this period (' . $this->prediction_percentage_on_long . '%).'));
        }
        else if ($this->hasNegativePrediction())
        {
            $this->addSignal(Signal::BAD('Negative prediction based in this period (' . $this->prediction_percentage_on_long . '%).'));
        }

        if ($this->hasLowVolatility())
        {
            $this->addSignal(Signal::NEUTRAL('Low volatility in this period (' . $this->max_change_percent . '%).'));
        }

        if ($this->isPredictionProgressivelyBetter())
        {
            $this->addSignal(Signal::GOOD('Better predictions on short term measurements.'));
        }

        if ($this->isPredictionProgressivelyWorse())
        {
            $this->addSignal(Signal::BAD('Worse predictions on short term measurements.'));
        }

        if ($this->isAllPositive())
        {
            $this->addSignal(Signal::EXCELLENT('Earning in all date ranges.'));
        }

        if ($this->isAllNegative())
        {
            $this->addSignal(Signal::POOR('Loosing in all date ranges.'));
        }

        if ($this->isRecoveringOnShort())
        {
            $this->addSignal(Signal::GOOD('Recovering value in short term (' . $this->change_percentage_on_short . '%) after being in down tendency.'));
        }

        if ($this->isLoosingOnShort())
        {
            $this->addSignal(Signal::BAD('Loosing value in short term (' . $this->change_percentage_on_short . '%) after being in up tendency.'));
        }

        if ($this->isNoticeableRecentImprovement())
        {
            $this->addSignal(Signal::GOOD('Having a noticeable improvement recently (' . $this->change_percentage_on_short . '%).'));
        }

        if ($this->isNoticeableRecentDecrease())
        {
            $this->addSignal(Signal::BAD('Having a noticeable decrease recently (' . $this->change_percentage_on_short . '%).'));
        }

        if ($this->isHighlyVolatile())
        {
            $this->addSignal(
                Signal::NEUTRAL('High volatility during this period: Max increase: ' . $this->max_change_amount . '%, Max. decrease: ' . $this->min_change_amount . '%.')
            );
        }

        return $this->getAllSignals();
    }

    private function hasLowVolatility(): bool
    {
        return $this->diff_change_percent < self::SENSIBILITY_THRESHOLD;
    }

    private function isPredictionProgressivelyBetter(): bool
    {
        return $this->prediction_percentage_on_medium > $this->prediction_percentage_on_long
            && $this->prediction_percentage_on_short > $this->prediction_percentage_on_medium
            && $this->diff_change_percent > self::SENSIBILITY_THRESHOLD;
    }

    private function isPredictionProgressivelyWorse(): bool
    {
        return $this->prediction_percentage_on_medium < $this->prediction_percentage_on_long
            && $this->prediction_percentage_on_short < $this->prediction_percentage_on_medium
            && $this->diff_change_percent > self::SENSIBILITY_THRESHOLD;
    }

    private function isAllPositive(): bool
    {
        return $this->change_percentage_on_long > self::SENSIBILITY_THRESHOLD
            && $this->change_percentage_on_medium > self::SENSIBILITY_THRESHOLD
            && $this->change_percentage_on_short > self::SENSIBILITY_THRESHOLD;
    }

    private function isAllNegative(): bool
    {
        return $this->change_percentage_on_long < -self::SENSIBILITY_THRESHOLD
            && $this->change_percentage_on_medium < -self::SENSIBILITY_THRESHOLD
            && $this->change_percentage_on_short < -self::SENSIBILITY_THRESHOLD;
    }

    private function isRecoveringOnShort(): bool
    {
        return $this->change_percentage_on_short > self::SENSIBILITY_THRESHOLD
            && $this->change_percentage_on_medium < 0
            && $this->change_percentage_on_long < 0;
    }

    private function isLoosingOnShort(): bool
    {
        return $this->change_percentage_on_short < -self::SENSIBILITY_THRESHOLD
            && $this->change_percentage_on_medium > 0
            && $this->change_percentage_on_long > 0;
    }

    private function isNoticeableRecentImprovement(): bool
    {
        return $this->change_percentage_on_short >= self::NOTICEABLE_CHANGES;
    }

    private function isNoticeableRecentDecrease(): bool
    {
        return $this->change_percentage_on_short <= -self::NOTICEABLE_CHANGES;
    }

    private function isHighlyVolatile(): bool
    {
        return $this->diff_change_percent >= self::VOLATILITY_THRESHOLD;
    }

    private function addSignal(Signal $signal): void
    {
        $this->signals[] = $signal;
    }

    private function setMaxMinAndDiff(): void
    {
        $this->max_change_amount  = null;
        $this->min_change_amount  = null;
        $this->max_change_percent = null;
        $this->min_change_percent = null;

        /** @var \Obokaman\StockForecast\Domain\Model\Financial\Stock\Measurement $stock_stats */
        foreach ($this->long_term_measurements as $stock_stats)
        {
            $change         = $stock_stats->change();
            $change_percent = $stock_stats->changePercent();

            if (null === $this->max_change_amount || $change > $this->max_change_amount)
            {
                $this->max_change_amount = $change;
            }
            if (null === $this->min_change_amount || $change < $this->min_change_amount)
            {
                $this->min_change_amount = $change;
            }
            if (null === $this->max_change_percent || $change_percent > $this->max_change_percent)
            {
                $this->max_change_percent = $change_percent;
            }
            if (null === $this->min_change_percent || $change_percent < $this->min_change_percent)
            {
                $this->min_change_percent = $change_percent;
            }
        }

        $this->diff_change_amount  = $this->max_change_amount - $this->min_change_amount;
        $this->diff_change_percent = $this->max_change_percent - $this->min_change_percent;
    }

    /** @return Signal[] */
    private function getAllSignals(): array
    {
        return $this->signals ?: [Signal::NEUTRAL('No relevant signals.')];
    }

    private function hasPositivePrediction(): bool
    {
        return $this->prediction_percentage_on_long > self::SENSIBILITY_THRESHOLD;
    }

    private function hasVeryPositivePrediction(): bool
    {
        return $this->prediction_percentage_on_long > self::SENSIBILITY_THRESHOLD * 5;
    }

    private function hasNegativePrediction(): bool
    {
        return $this->prediction_percentage_on_long < -self::SENSIBILITY_THRESHOLD;
    }

    private function hasVeryNegativePrediction(): bool
    {
        return $this->prediction_percentage_on_long < -self::SENSIBILITY_THRESHOLD * 5;
    }
}
