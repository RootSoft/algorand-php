<?php


namespace Rootsoft\Algorand\Models\Ledgers;

class LedgerSupplyResult
{

    /**
     * @var int
     * @required
     */
    public int $current_round;

    /**
     * @var int
     * @required
     */
    public int $onlineMoney;

    /**
     * @var int
     * @required
     */
    public int $totalMoney;

}
