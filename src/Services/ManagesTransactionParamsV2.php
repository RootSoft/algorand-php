<?php


namespace Rootsoft\Algorand\Services;

use Rootsoft\Algorand\Models\Transactions\TransactionParams;

trait ManagesTransactionParamsV2
{
    /**
     * Get the suggested parameters for constructing a new transaction.
     *
     * @return \Rootsoft\Algorand\Models\Transactions\TransactionParams
     */
    public function getSuggestedTransactionParams()
    {
        $response = $this->get($this->algodClient, "/v2/transactions/params");

        return $this->jsonMapper->map($response, new TransactionParams());
    }
}
