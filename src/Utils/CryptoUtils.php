<?php

namespace Rootsoft\Algorand\Utils;

use ParagonIE\Halite\Asymmetric\SignatureSecretKey;

class CryptoUtils
{
    /**
     * Generate the hash for the given data.
     * @param $data
     * @return string
     */
    public static function sha512256($data)
    {
        return hash('sha512/256', $data, true);
    }

    /**
     * Generate the hash for the given data using sha256.
     * @param $data
     * @return string
     */
    public static function sha256($data)
    {
        return hash('sha256', $data, true);
    }

    /**
     * Verify a message with the given signature.
     * @param $message
     * @param $signature
     * @param $publicKey
     * @return bool
     * @throws \SodiumException
     */
    public static function verify(string $message, string $signature, string $publicKey) : bool
    {
        return sodium_crypto_sign_verify_detached($signature, $message, $publicKey);
    }

    /**
     * Sign a message with the given secret key.
     *
     * @param string $message
     * @param SignatureSecretKey $secretKey
     * @return string
     * @throws \SodiumException
     */
    public static function sign(string $message, SignatureSecretKey $secretKey): string
    {
        return \sodium_crypto_sign_detached(
            $message,
            $secretKey->getRawKeyMaterial()
        );
    }

    public static function fillBytes(int $value, int $size = 32)
    {
        return array_fill(0, $size, $value);
    }
}
