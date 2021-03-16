<?php


namespace Rootsoft\Algorand\Indexer\Builders;

use DateTime;
use JsonMapper;
use ParagonIE\ConstantTime\Base64;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Indexer\QueryBuilder;
use Rootsoft\Algorand\Indexer\Services\ManagesIndexerTransactionsV2;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Addresses\AddressRole;
use Rootsoft\Algorand\Models\SignatureType;
use Rootsoft\Algorand\Models\Transactions\SearchTransactionsResult;
use Rootsoft\Algorand\Models\Transactions\TransactionType;
use Rootsoft\Algorand\Traits\MakesHttpRequests;

class TransactionQueryBuilder extends QueryBuilder
{
    use MakesHttpRequests;
    use ManagesIndexerTransactionsV2;

    /**
     * AlgorandIndexer constructor.
     * @param IndexerClient $indexerClient
     * @param \JsonMapper $jsonMapper
     */
    public function __construct(IndexerClient $indexerClient, JsonMapper $jsonMapper)
    {
        parent::__construct($indexerClient, $jsonMapper);
    }

    /**
     * Lookup transactions for the given asset.
     *
     * @param int $assetId
     * @return $this
     */
    public function forAsset(int $assetId)
    {
        $this->setParameter('for_asset_id', $assetId);

        return $this;
    }

    /**
     * Lookup transaction for the given account.
     *
     * @param string $accountId
     * @return $this
     */
    public function forAccount(string $accountId)
    {
        $this->setParameter('for_account_id', $accountId);

        return $this;
    }

    /**
     * Only include transactions with this address in one of the transaction fields.
     *
     * @param Address $address
     * @return $this
     */
    public function whereAddress(Address $address)
    {
        $this->setParameter('address', $address->encodedAddress);

        return $this;
    }

    /**
     * Combine with the address parameter to define what type of address to search for.
     *
     * @param AddressRole $role
     * @return $this
     */
    public function whereAddressRole(AddressRole $role)
    {
        $this->setParameter('address-role', $role->getValue());

        return $this;
    }

    /**
     * Results should have an amount greater than this value.
     * MicroAlgos are the default currency unless an asset-id is provided, in which case the asset will be
     * used.
     *
     * @param int $amount
     * @return $this
     */
    public function whereCurrencyIsGreaterThan(int $amount)
    {
        $this->setParameter('currency-greater-than', $amount);

        return $this;
    }

    /**
     * Results should have an amount less than this value.
     * MicroAlgos are the default currency unless an asset-id is provided, in which case the asset will be
     * used.
     *
     * @param int $currency
     * @return $this
     */
    public function whereCurrencyIsLessThan(int $currency)
    {
        $this->setParameter('currency-less-than', $currency);

        return $this;
    }

    /**
     * Include results after the given time.
     * Must be an RFC 3339 formatted string.
     *
     * @param DateTime $dateTime
     * @return $this
     */
    public function after(DateTime $dateTime)
    {
        $this->setParameter('after-time', $dateTime->format(DateTime::RFC3339));

        return $this;
    }

    /**
     * Include results before the given time.
     * Must be an RFC 3339 formatted string.
     *
     * @param DateTime $dateTime
     * @return $this
     */
    public function before(DateTime $dateTime)
    {
        $this->setParameter('before-time', $dateTime->format(DateTime::RFC3339));

        return $this;
    }

    /**
     * Combine with address and address-role parameters to define what type of address
     * to search for. The close to fields are normally treated as a receiver, if you
     * would like to exclude them set this parameter to true.
     *
     * @param bool $excludeCloseTo
     * @return $this
     */
    public function excludeCloseTo(bool $excludeCloseTo)
    {
        $this->setParameter('exclude-close-to', $excludeCloseTo);

        return $this;
    }

    /**
     * Include results for the specified round.
     *
     * @param int $round
     * @return $this
     */
    public function whereRound(int $round)
    {
        $this->setParameter('round', $round);

        return $this;
    }

    /**
     * Include results at or before the specified max-round.
     *
     * @param int $maxRound
     * @return $this
     */
    public function beforeMaxRound(int $maxRound)
    {
        $this->setParameter('max-round', $maxRound);

        return $this;
    }

    /**
     * Include results at or after the specified min-round.
     *
     * @param int $minRound
     * @return $this
     */
    public function afterMinRound(int $minRound)
    {
        $this->setParameter('min-round', $minRound);

        return $this;
    }

    /**
     * Include results with the given application id.
     *
     * @param int $applicationId
     * @return $this
     */
    public function whereApplicationId(int $applicationId)
    {
        $this->setParameter('application-id', $applicationId);

        return $this;
    }

    /**
     * Include results with the given asset id.
     *
     * @param int $assetId
     * @return $this
     */
    public function whereAssetId(int $assetId)
    {
        $this->setParameter('asset-id', $assetId);

        return $this;
    }

    /**
     * Specifies a prefix which must be contained in the note field.
     *
     * @param string $notePrefix
     * @return $this
     */
    public function whereNotePrefix(string $notePrefix)
    {
        $this->setParameter('note-prefix', Base64::encode($notePrefix));

        return $this;
    }

    /**
     * Include results which include the rekey-to field.
     *
     * @param bool $rekeyTo
     * @return $this
     */
    public function rekeyTo(bool $rekeyTo)
    {
        $this->setParameter('rekey-to', $rekeyTo);

        return $this;
    }

    /**
     * SigType filters just results using the specified type of signature:
     *   sig - Standard
     *   msig - MultiSig
     *   lsig - LogicSig
     *
     * @param SignatureType $signatureType
     * @return $this
     */
    public function whereSignatureType(SignatureType $signatureType)
    {
        $this->setParameter('sig-type', $signatureType->getValue());

        return $this;
    }

    /**
     * Include results only with the given transaction type.
     *
     * @param TransactionType $transactionType
     * @return $this
     */
    public function whereTransactionType(TransactionType $transactionType)
    {
        $this->setParameter('tx-type', $transactionType->getValue());

        return $this;
    }

    /**
     * Include results only with the given transaction id.
     *
     * @param string $transactionId
     * @return $this
     */
    public function whereTransactionId(string $transactionId)
    {
        $this->setParameter('txid', $transactionId);

        return $this;
    }

    /**
     * Fetch the transactions.
     *
     * @param int|null $limit
     * @return SearchTransactionsResult
     */
    public function search(?int $limit = null)
    {
        if (! is_null($limit)) {
            $this->limit($limit);
        }

        return $this->searchTransactions($this->payload);
    }
}
