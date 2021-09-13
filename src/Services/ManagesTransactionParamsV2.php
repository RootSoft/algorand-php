<?php

namespace Rootsoft\Algorand\Services;

use JsonMapper\JsonMapperInterface;
use Rootsoft\Algorand\Models\Transactions\TransactionParams;

trait ManagesTransactionParamsV2
{
    private JsonMapperInterface $jsonMapper;

    /**
     * Get the suggested parameters for constructing a new transaction.
     *
     * @return \Rootsoft\Algorand\Models\Transactions\TransactionParams
     */
    public function getSuggestedTransactionParams()
    {
        $response = $this->get($this->algodClient, '/v2/transactions/params');

        $params = new TransactionParams();
        $this->jsonMapper->mapObject($response, $params);

        return $params;
    }
}
