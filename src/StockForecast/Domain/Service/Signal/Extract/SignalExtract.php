<?php

namespace Obokaman\StockForecast\Domain\Service\Signal\Extract;

use Obokaman\StockForecast\Domain\Model\Financial\Signal;

interface SignalExtract
{
    /**
     * @param SignalExtractRequest $a_request
     *
     * @return null|Signal[]
     */
    public function extract(SignalExtractRequest $a_request): ?array;
}