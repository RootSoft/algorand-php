<?php

namespace Rootsoft\Algorand\Services;

use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Keys\ParticipationPublicKey;
use Rootsoft\Algorand\Models\Keys\VRFPublicKey;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;

trait ManagesParticipationV2
{
    /**
     * Register an account online.
     *
     * @param Account $account
     * @param ParticipationPublicKey $votePK
     * @param VRFPublicKey $selectionPK
     * @param int $voteFirst
     * @param int $voteLast
     * @param int $voteKeyDilution
     * @param bool $waitForConfirmation
     * @param int $timeout
     *
     * @return \Rootsoft\Algorand\Models\Transactions\PendingTransaction|string
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public function registerOnline(
        Account $account,
        ParticipationPublicKey $votePK,
        VRFPublicKey $selectionPK,
        int $voteFirst,
        int $voteLast,
        int $voteKeyDilution = 10000,
        bool $waitForConfirmation = false,
        int $timeout = 5
    ) {
        // Get the suggested transaction params
        $params = $this->getSuggestedTransactionParams();

        // Create a new asset
        $transaction = TransactionBuilder::keyRegistration()
            ->sender($account->getAddress())
            ->votePublicKey($votePK)
            ->selectionPublicKey($selectionPK)
            ->voteFirst($voteFirst)
            ->voteLast($voteLast)
            ->voteKeyDilution($voteKeyDilution)
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        return $this->sendTransaction($signedTransaction, $waitForConfirmation, $timeout);
    }

    /**
     * Register an account offline.
     *
     * @param Account $account
     * @param bool $waitForConfirmation
     * @param int $timeout
     *
     * @return \Rootsoft\Algorand\Models\Transactions\PendingTransaction|string
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public function registerOffline(
        Account $account,
        bool $waitForConfirmation = false,
        int $timeout = 5
    ) {
        // Get the suggested transaction params
        $params = $this->getSuggestedTransactionParams();

        // Create a new asset
        $transaction = TransactionBuilder::keyRegistration()
            ->sender($account->getAddress())
            ->votePublicKey(null)
            ->selectionPublicKey(null)
            ->voteFirst(null)
            ->voteLast(null)
            ->voteKeyDilution(null)
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        return $this->sendTransaction($signedTransaction, $waitForConfirmation, $timeout);
    }
}
