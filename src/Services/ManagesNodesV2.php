<?php


namespace Rootsoft\Algorand\Services;

use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\GenesisInformation;
use Rootsoft\Algorand\Models\NodeStatus;

trait ManagesNodesV2
{
    /**
     * Gets the genesis information
     *
     * @return \Rootsoft\Algorand\Models\GenesisInformation The entire genesis file.
     * @throws AlgorandException
     */
    public function genesis()
    {
        $response = $this->get($this->algodClient, '/genesis');

        return $this->jsonMapper->map($response, new GenesisInformation());
    }

    /**
     * Checks if the node is healthy.
     *
     * @return bool True if healthy.
     * @throws AlgorandException
     */
    public function health()
    {
        $this->get($this->algodClient, '/health');

        return true;
    }

    /**
     * Gets the current node status.
     *
     * @return \Rootsoft\Algorand\Models\NodeStatus The status of the Node.
     * @throws AlgorandException
     */
    public function status()
    {
        $response = $this->get($this->algodClient, '/v2/status');

        return $this->jsonMapper->map($response, new NodeStatus());
    }

    /**
     * Gets the node status after waiting for the given round.
     *
     * Waits for a block to appear after round {round} and returns the node's status at the time.
     *
     * @param int $round The round to wait until returning status
     * @return \Rootsoft\Algorand\Models\NodeStatus The status of the Node.
     */
    public function statusAfterRound(int $round)
    {
        $response = $this->get($this->algodClient, "/v2/status/wait-for-block-after/$round");

        return $this->jsonMapper->map($response, new NodeStatus());
    }
}
