<?php


namespace Rootsoft\Algorand\Traits;

use ParagonIE\Halite\Alerts\InvalidKey;
use ParagonIE\Halite\Asymmetric\SecretKey;
use ParagonIE\Halite\Asymmetric\SignatureSecretKey;
use ParagonIE\Halite\SignatureKeyPair;
use ParagonIE\Halite\Util;
use ParagonIE\HiddenString\HiddenString;

final class KeyPairGenerator
{
    /**
     * The length of the seed bytes.
     */
    const SEED_BYTES_LENGTH = 32;

    /**
     * Derive a key pair for public key digital signatures from a single key seed.
     *
     * @param string $seed
     * @return SignatureKeyPair
     * @throws InvalidKey
     * @throws \SodiumException
     */
    public static function deriveSignatureKeyPairFromSeed(string $seed): SignatureKeyPair
    {
        // Encryption keypair
        $kp = \sodium_crypto_sign_seed_keypair($seed);
        $secretKey = \sodium_crypto_sign_secretkey($kp);

        // Let's wipe our $kp variable
        Util::memzero($kp);

        return new SignatureKeyPair(
            new SignatureSecretKey(
                new HiddenString($secretKey)
            )
        );
    }

    /**
     * The secret key actually includes the seed (either a random seed or the one given to crypto_sign_seed_keypair())
     * as well as the public key.
     *
     * While the public key can always be derived from the seed,
     * the precomputation saves a significant amount of CPU cycles when signing.
     *
     * @param SecretKey $secretKey
     * @return string
     */
    public static function deriveSeedFromSignatureSecretKey(SecretKey $secretKey): string
    {
        // TODO Implementation crypto_sign_ed25519_sk_to_seed ?
        $seed = substr($secretKey->getRawKeyMaterial(), 0, self::SEED_BYTES_LENGTH);

        return $seed;
    }
}
