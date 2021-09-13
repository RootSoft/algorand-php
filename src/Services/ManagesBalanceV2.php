<?php

namespace Rootsoft\Algorand\Services;

use Rootsoft\Algorand\Models\Accounts\AccountInformation;

trait ManagesBalanceV2
{
    /**
     * Get the balance (in microAlgos) of the given address.
     *
     * @param string $address
     * @return int
     */
    public function getBalance(string $address)
    {
        $response = $this->get($this->algodClient, "/v2/accounts/$address");

        $accountInformation = new AccountInformation();
        $this->jsonMapper->mapObject($response, $accountInformation);

        return $accountInformation->amountWithoutPendingRewards;
    }
}
