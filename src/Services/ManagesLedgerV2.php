<?php


namespace Rootsoft\Algorand\Services;

use Rootsoft\Algorand\Models\Ledgers\LedgerSupplyResult;

trait ManagesLedgerV2
{
    /**
     * Get the current supply reported by the ledger.
     *
     * Supply represents the current supply of MicroAlgos in the system.
     *
     * @return \Rootsoft\Algorand\Models\Ledgers\LedgerSupplyResult
     */
    public function getSupply()
    {
        $response = $this->get($this->algodClient, "/v2/ledger/supply");

        return $this->jsonMapper->map($response, new LedgerSupplyResult());
    }
}
