<?php

namespace Obokaman\StockForecast\Domain\Service\Signal\Extract;


use Obokaman\StockForecast\Domain\Model\Financial\Stock\Measurement;
use Obokaman\StockForecast\Domain\Model\Financial\Stock\MeasurementCollection;

class SignalExtractRequest
{
    public $long_term_measurements;
    public $mid_term_measurements;
    public $short_term_measurements;
    public $change_percentage_on_long;
    public $change_percentage_on_medium;
    public $change_percentage_on_short;
    public $prediction_percentage_on_long;
    public $prediction_percentage_on_medium;
    public $prediction_percentage_on_short;
    public $max_change_amount;
    public $min_change_amount;
    public $diff_change_amount;
    public $max_change_percent;
    public $min_change_percent;
    public $diff_change_percent;

    public function __construct(
        MeasurementCollection $long_term_measurements,
        MeasurementCollection $mid_term_measurements,
        MeasurementCollection $short_term_measurements,
        float $change_percentage_on_long,
        float $change_percentage_on_medium,
        float $change_percentage_on_short,
        float $prediction_percentage_on_long,
        float $prediction_percentage_on_medium,
        float $prediction_percentage_on_short
    ) {
        $this->long_term_measurements          = $long_term_measurements;
        $this->mid_term_measurements           = $mid_term_measurements;
        $this->short_term_measurements         = $short_term_measurements;
        $this->change_percentage_on_long       = $change_percentage_on_long;
        $this->change_percentage_on_medium     = $change_percentage_on_medium;
        $this->change_percentage_on_short      = $change_percentage_on_short;
        $this->prediction_percentage_on_long   = $prediction_percentage_on_long;
        $this->prediction_percentage_on_medium = $prediction_percentage_on_medium;
        $this->prediction_percentage_on_short  = $prediction_percentage_on_short;
        $this->setMaxMinAndDiffPriceChanges();
    }

    private function setMaxMinAndDiffPriceChanges(): void
    {
        $this->max_change_amount  = null;
        $this->min_change_amount  = null;
        $this->max_change_percent = null;
        $this->min_change_percent = null;

        /** @var Measurement $stock_stats */
        foreach ($this->long_term_measurements as $stock_stats) {
            $change         = $stock_stats->change();
            $change_percent = $stock_stats->changePercent();

            if (null === $this->max_change_amount || $change > $this->max_change_amount) {
                $this->max_change_amount = $change;
            }
            if (null === $this->min_change_amount || $change < $this->min_change_amount) {
                $this->min_change_amount = $change;
            }
            if (null === $this->max_change_percent || $change_percent > $this->max_change_percent) {
                $this->max_change_percent = $change_percent;
            }
            if (null === $this->min_change_percent || $change_percent < $this->min_change_percent) {
                $this->min_change_percent = $change_percent;
            }
        }

        $this->diff_change_amount  = $this->max_change_amount - $this->min_change_amount;
        $this->diff_change_percent = $this->max_change_percent - $this->min_change_percent;
    }
}