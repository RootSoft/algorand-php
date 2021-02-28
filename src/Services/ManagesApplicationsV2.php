<?php


namespace Rootsoft\Algorand\Services;

use Rootsoft\Algorand\Models\Application;

trait ManagesApplicationsV2
{
    /**
     * Gets application information.
     *
     * Given a application id, it returns application information including creator, approval and clear programs,
     * global and local schemas, and global state.
     *
     * @param int $applicationId
     * @return \Rootsoft\Algorand\Models\Application
     */
    public function getApplicationById(int $applicationId)
    {
        $response = $this->get($this->algodClient, "/v2/applications/$applicationId");

        return $this->jsonMapper->map($response, new Application());
    }
}
