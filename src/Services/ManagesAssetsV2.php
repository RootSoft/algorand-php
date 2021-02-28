<?php


namespace Rootsoft\Algorand\Services;

use Brick\Math\BigInteger;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Assets\AssetResult;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use SodiumException;

trait ManagesAssetsV2
{
    use ManagesTransactionsV2;
    use ManagesTransactionParamsV2;

    /**
     * Gets the asset information.
     *
     * Given a asset id, it returns asset information including creator, name, total supply and special addresses.
     *
     * @param int $assetId
     * @return AssetResult
     */
    public function getAssetById(int $assetId)
    {
        $response = $this->get($this->indexerClient, "/v2/assets/$assetId");

        return $this->jsonMapper->map($response, new AssetResult());
    }

    /**
     * Create a new asset (Algorand Standard Asset).
     * Helper function to create a new asset.
     *
     * With Algorand Standard Assets you can represent stablecoins, loyalty points, system credits, and in-game points,
     * just to name a few examples.
     *
     * Fungible asset: Assets that represent many of the same type, like a stablecoin
     * Non-fungible asset: Single, unique assets.
     *
     * @param Account $account The account used to create the asset.
     * @param string $assetName The name of the asset (Algorand)
     * @param string $unitName The unit name of the asset (e.g ALGO)
     * @param int $totalAssets The number of total assets.
     * @param int $decimals The number of decimals
     * @param bool $defaultFrozen
     * @param Address|null $managerAddress
     * @param Address|null $reserveAddress
     * @param Address|null $freezeAddress
     * @param Address|null $clawbackAddress
     *
     * @return string The transaction id.
     * @throws AlgorandException
     * @throws SodiumException
     */
    public function createNewAsset(
        Account $account,
        string $assetName,
        string $unitName,
        int $totalAssets,
        int $decimals,
        bool $defaultFrozen = false,
        Address $managerAddress = null,
        Address $reserveAddress = null,
        Address $freezeAddress = null,
        Address $clawbackAddress = null
    ) {
        $address = $account->getAddress();

        // Get the suggested transaction params
        $params = $this->getSuggestedTransactionParams();

        // Create a new asset
        $transaction = TransactionBuilder::assetConfig()
            ->assetName($assetName)
            ->unitName($unitName)
            ->totalAssetsToCreate(BigInteger::of($totalAssets))
            ->decimals($decimals)
            ->defaultFrozen($defaultFrozen)
            ->managerAddress($managerAddress ?? $address)
            ->reserveAddress($reserveAddress ?? $address)
            ->freezeAddress($freezeAddress ?? $address)
            ->clawbackAddress($clawbackAddress ?? $address)
            ->sender($address)
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        return $this->sendTransaction($signedTransaction);
    }

    /**
     * Edit an existing asset.
     *
     * After an asset has been created only the manager, reserve, freeze and clawback accounts can be changed.
     * All other parameters are locked for the life of the asset.
     *
     * If any of these addresses are set to "" that address will be cleared and can never be reset for the life of the asset.
     * Only the manager account can make configuration changes and must authorize the transaction.
     *
     * @param int $assetId
     * @param Account $account
     * @param Address|null $managerAddress
     * @param Address|null $reserveAddress
     * @param Address|null $freezeAddress
     * @param Address|null $clawbackAddress
     *
     * @return string The id of the transaction.
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public function editAsset(
        int $assetId,
        Account $account,
        Address $managerAddress = null,
        Address $reserveAddress = null,
        Address $freezeAddress = null,
        Address $clawbackAddress = null
    ) {
        $address = $account->getAddress();

        // Get the suggested transaction params
        $params = $this->getSuggestedTransactionParams();

        // Create a new asset
        $transaction = TransactionBuilder::assetConfig()
            ->assetId(BigInteger::of($assetId))
            ->managerAddress($managerAddress)
            ->reserveAddress($reserveAddress)
            ->freezeAddress($freezeAddress)
            ->clawbackAddress($clawbackAddress)
            ->sender($address)
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        return $this->sendTransaction($signedTransaction);
    }

    /**
     * Destroy (remove) an existing asset from the Algorand ledger.
     *
     * A Destroy Transaction is issued to remove an asset from the Algorand ledger.
     * To destroy an existing asset on Algorand, the original creator must be in possession of all units of the asset
     * and the manager must send and therefore authorize the transaction.
     *
     * @param int $assetId
     * @param Account $account
     *
     * @return string The id of the transaction
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public function destroy(int $assetId, Account $account)
    {
        $address = $account->getAddress();

        // Get the suggested transaction params
        $params = $this->getSuggestedTransactionParams();

        // Destroy the asset
        $transaction = TransactionBuilder::assetConfig()
            ->assetId(BigInteger::of($assetId))
            ->sender($address)
            ->suggestedParams($params)
            ->destroy();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        return $this->sendTransaction($signedTransaction);
    }

    /**
     * Opt-in to receive an asset
     * An opt-in transaction is simply an asset transfer with an amount of 0, both to and from the account opting in.
     *
     * Assets can be transferred between accounts that have opted-in to receiving the asset.
     * These are analogous to standard payment transactions but for Algorand Standard Assets.
     *
     * @param int $assetId
     * @param Account $account
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public function optIn(int $assetId, Account $account)
    {
        $address = $account->getAddress();

        // Get the suggested transaction params
        $params = $this->getSuggestedTransactionParams();

        // Opt-in to the asset
        $transaction = TransactionBuilder::assetTransfer()
            ->assetId(BigInteger::of($assetId))
            ->assetReceiver($address)
            ->sender($address)
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        $this->sendTransaction($signedTransaction);
    }

    /**
     * Transfer an asset from the account to the receiver.
     * Assets can be transferred between accounts that have opted-in to receiving the asset.
     * These are analogous to standard payment transactions but for Algorand Standard Assets.
     *
     * @param int $assetId
     * @param Account $account
     * @param int $amount
     * @param Address $receiver
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public function transfer(int $assetId, Account $account, int $amount, Address $receiver)
    {
        $address = $account->getAddress();

        // Get the suggested transaction params
        $params = $this->getSuggestedTransactionParams();

        // Transfer the assets
        $transaction = TransactionBuilder::assetTransfer()
            ->assetId(BigInteger::of($assetId))
            ->amount($amount)
            ->assetReceiver($receiver)
            ->sender($address)
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        $this->sendTransaction($signedTransaction);
    }

    /**
     * Freeze or unfreeze an asset for an account.
     * Freezing or unfreezing an asset requires a transaction that is signed by the freeze account.
     *
     * Upon creation of an asset, you can specify a freeze address and a defaultfrozen state.
     * If the defaultfrozen state is set to true the corresponding freeze address must issue unfreeze transactions,
     * to allow trading of the asset to and from that account.
     * This may be useful in situations that require holders of the asset to pass certain checks prior to ownership.
     *
     * If the defaultfrozen state is set to false anyone would be allowed to trade the asset and the freeze address
     * could issue freeze transactions to specific accounts to disallow trading of that asset.
     *
     * If you want to ensure to asset holders that the asset will never be frozen, set the defaultfrozen state to false
     * and set the freeze address to null or an empty string in goal and the SDKs.
     *
     * @param int $assetId
     * @param Account $account
     * @param Address $freezeTarget
     * @param bool $freeze
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public function freeze(int $assetId, Account $account, Address $freezeTarget, bool $freeze)
    {
        $address = $account->getAddress();

        // Get the suggested transaction params
        $params = $this->getSuggestedTransactionParams();

        // (Un)freeze the assets
        $transaction = TransactionBuilder::assetFreeze()
            ->assetId(BigInteger::of($assetId))
            ->freezeTarget($freezeTarget)
            ->freeze($freeze)
            ->sender($address)
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        $this->sendTransaction($signedTransaction);
    }

    /**
     * Revokes an asset for a given account.
     *
     * Revoking an asset for an account removes a specific number of the asset from the revoke target account.
     * Revoking an asset from an account requires specifying an asset sender (the revoke target account) and an
     * asset receiver (the account to transfer the funds back to).
     *
     * The clawback address, if specified, is able to revoke the asset from any account and place them in any other
     * account that has previously opted-in.
     *
     * This may be useful in situations where a holder of the asset breaches some set of terms that you established
     * for that asset. You could issue a freeze transaction to investigate, and if you determine that they can no longer
     * own the asset, you could revoke the assets.
     *
     * Similar to freezing, if you would rather ensure to asset holders that you will never have the ability to
     * revoke assets, set the clawback address to null.
     *
     * @param int $assetId
     * @param Account $account
     * @param int $amount
     * @param Address $revokeAddress
     * @param Address|null $clawbackAddress
     * @throws AlgorandException
     * @throws SodiumException
     */
    public function revoke(int $assetId, Account $account, int $amount, Address $revokeAddress, ?Address $clawbackAddress = null)
    {
        $address = $account->getAddress();

        // Get the suggested transaction params
        $params = $this->getSuggestedTransactionParams();

        // Revoke the assets
        $transaction = TransactionBuilder::assetTransfer()
            ->assetId(BigInteger::of($assetId))
            ->amount($amount)
            ->assetSender($revokeAddress) // address of the account from which the assets will be revoked
            ->assetReceiver($clawbackAddress ?? $address) // Address which receives the revoked assets
            ->sender($address)
            ->suggestedParams($params)
            ->build();

        // Sign the transaction
        $signedTransaction = $transaction->sign($account);

        // Broadcast the transaction on the network
        $this->sendTransaction($signedTransaction);
    }
}
