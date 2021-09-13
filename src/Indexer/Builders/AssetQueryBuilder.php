<?php

namespace Rootsoft\Algorand\Indexer\Builders;

use JsonMapper\JsonMapperInterface;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Indexer\QueryBuilder;
use Rootsoft\Algorand\Indexer\Services\ManagesIndexerAssetsV2;
use Rootsoft\Algorand\Models\Assets\SearchAssetsResult;
use Rootsoft\Algorand\Traits\MakesHttpRequests;

class AssetQueryBuilder extends QueryBuilder
{
    use MakesHttpRequests;
    use ManagesIndexerAssetsV2;

    /**
     * AlgorandIndexer constructor.
     *
     * @param IndexerClient $client
     * @param JsonMapperInterface $jsonMapper
     */
    public function __construct(IndexerClient $client, JsonMapperInterface $jsonMapper)
    {
        parent::__construct($client, $jsonMapper);
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
     * Filter just assets with the given creator address.
     *
     * @param string $creator
     * @return $this
     */
    public function whereCreator(string $creator)
    {
        $this->setParameter('creator', $creator);

        return $this;
    }

    /**
     * Filter just assets with the given asset name.
     *
     * @param string $name
     * @return $this
     */
    public function whereAssetName(string $name)
    {
        $this->setParameter('name', $name);

        return $this;
    }

    /**
     * Filter just assets with the given unit name.
     *
     * @param string $unitName
     * @return $this
     */
    public function whereUnitName(string $unitName)
    {
        $this->setParameter('unit', $unitName);

        return $this;
    }

    /**
     * Fetch the assets.
     *
     * @param int|null $limit
     * @return SearchAssetsResult
     */
    public function search(?int $limit = null)
    {
        if (! is_null($limit)) {
            $this->limit($limit);
        }

        return $this->searchAssets($this->payload);
    }
}
