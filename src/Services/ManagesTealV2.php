<?php


namespace Rootsoft\Algorand\Services;

use Rootsoft\Algorand\Models\Teals\TealCompilationResult;

trait ManagesTealV2
{
    /**
     * Compile TEAL source code to binary, produce its hash.
     *
     * Given TEAL source code in plain text,
     * return base64 encoded program bytes and base32 SHA512_256 hash of program bytes (Address style).
     *
     * This endpoint is only enabled when a node's configuration file sets EnableDeveloperAPI to true.
     *
     * @param string $teal
     * @return \Rootsoft\Algorand\Models\Teals\TealCompilationResult
     */
    public function compileTEAL(string $teal)
    {
        $response = $this->post($this->algodClient, "/v2/teal/compile", [], ['body' => $teal], ['Content-Type' => 'application/x-binary']);

        $result = new TealCompilationResult();
        $this->jsonMapper->mapObject($response, $result);

        return $result;
    }

}
