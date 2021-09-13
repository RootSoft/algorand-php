<?php

namespace Rootsoft\Algorand\Services;

use Rootsoft\Algorand\Models\Catchups\CatchupResult;

trait ManagesCatchupsV2
{
    /**
     * Starts a catchpoint catchup.
     *
     * Given a catchpoint, it starts catching up to this catchpoint.
     *
     * @param string $catchpoint A catch point.
     * @return \Rootsoft\Algorand\Models\Catchups\CatchupResult
     */
    public function startCatchup(string $catchpoint)
    {
        $response = $this->post($this->algodClient, "/v2/catchup/$catchpoint");

        $result = new CatchupResult();
        $this->jsonMapper->mapObject($response, $result);

        return $result;
    }
}
