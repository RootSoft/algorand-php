<?php

namespace Rootsoft\Algorand\Utils;

class Crypto
{
    /**
     * Verify a message with the given signature.
     * @param $message
     * @param $signature
     * @param $publicKey
     * @return bool
     * @throws \SodiumException
     */
    public static function verify($message, $signature, $publicKey) : bool
    {
        return sodium_crypto_sign_verify_detached($signature, $message, $publicKey);
    }
}
