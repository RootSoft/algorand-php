<?php

namespace Rootsoft\Algorand\Indexer\Builders;

use JsonMapper\JsonMapperInterface;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Indexer\QueryBuilder;
use Rootsoft\Algorand\Indexer\Services\ManagesIndexerAccountsV2;
use Rootsoft\Algorand\Models\Accounts\SearchAccountsResult;
use Rootsoft\Algorand\Traits\MakesHttpRequests;

class AccountQueryBuilder extends QueryBuilder
{
    use MakesHttpRequests;
    use ManagesIndexerAccountsV2;

    /**
     * AccountQueryBuilder constructor.
     *
     * @param IndexerClient $client
     * @param JsonMapperInterface $jsonMapper
     */
    public function __construct(IndexerClient $client, JsonMapperInterface $jsonMapper)
    {
        parent::__construct($client, $jsonMapper);
    }

    /**
     * Lookup the list of accounts who hold the given asset.
     *
     * @param int $assetId
     * @return $this
     */
    public function balances(int $assetId)
    {
        $this->setParameter('balance-asset-id', $assetId);

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
     * Include accounts configured to use this spending key.
     *
     * @param string $authAddress
     * @return AccountQueryBuilder
     */
    public function whereAuthAddress(string $authAddress)
    {
        $this->setParameter('auth-addr', $authAddress);

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
     * Fetch the accounts.
     *
     * @param int|null $limit
     * @return SearchAccountsResult
     */
    public function search(?int $limit = null)
    {
        if (! is_null($limit)) {
            $this->limit($limit);
        }

        return $this->searchAccounts($this->payload);
    }
}
