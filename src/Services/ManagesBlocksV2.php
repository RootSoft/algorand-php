<?php


namespace Rootsoft\Algorand\Services;

use Rootsoft\Algorand\Models\Blocks\BlockResult;

trait ManagesBlocksV2
{
    /**
     * Get the block for the given round.
     *
     * @param int $round The round from which to fetch block information.
     * @return \Rootsoft\Algorand\Models\Blocks\BlockResult
     */
    public function getBlock(int $round)
    {
        $response = $this->get($this->indexerClient, "/v2/blocks/$round");

        $result = new BlockResult();
        $this->jsonMapper->mapObject($response, $result);

        return $result;
    }
}
