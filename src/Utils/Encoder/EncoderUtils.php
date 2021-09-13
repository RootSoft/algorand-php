<?php

namespace Rootsoft\Algorand\Utils\Encoder;

use Brick\Math\BigInteger;
use Rootsoft\Algorand\Crypto\Signature;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Applications\StateSchema;
use Rootsoft\Algorand\Models\Applications\TEALProgram;
use Rootsoft\Algorand\Models\Keys\ParticipationPublicKey;
use Rootsoft\Algorand\Models\Keys\VRFPublicKey;

class EncoderUtils
{
    /**
     * Encode an address from a binary string to an Address object.
     *
     * @param string|null $address
     * @return Address|null
     * @throws \SodiumException
     */
    public static function toAddress(?string $address): ?Address
    {
        return $address ? Address::fromPublicKey($address) : null;
    }

    /**
     * Encode a signature from a binary string to an Signature object.
     *
     * @param string|null $signature
     * @return Address|null
     * @throws \Rootsoft\Algorand\Exceptions\AlgorandException
     */
    public static function toSignature(?string $signature): ?Signature
    {
        return $signature ? new Signature($signature) : null;
    }

    /**
     * Parse a value to a big int.
     *
     * @param $value
     * @return BigInteger|null
     */
    public static function toBigInteger($value): ?BigInteger
    {
        return $value ? BigInteger::of($value) : null;
    }

    /**
     * Encode a key from a binary string to a ParticipationPublicKey object.
     *
     * @param string|null $publicKey
     * @return ParticipationPublicKey|null
     * @throws \Rootsoft\Algorand\Exceptions\AlgorandException
     */
    public static function toParticipationPublicKey(?string $publicKey): ?ParticipationPublicKey
    {
        return $publicKey ? new ParticipationPublicKey($publicKey) : null;
    }

    /**
     * Encode a key from a binary string to a VRFPublicKey object.
     *
     * @param string|null $publicKey
     * @return VRFPublicKey|null
     * @throws \Rootsoft\Algorand\Exceptions\AlgorandException
     */
    public static function toVRFPublicKey(?string $publicKey): ?VRFPublicKey
    {
        return $publicKey ? new VRFPublicKey($publicKey) : null;
    }

    /**
     * Encode a binary string to a TEAL program, or returns null;.
     *
     * @param string|null $value
     * @return TEALProgram|null
     */
    public static function toTEALProgram(?string $value): ?TEALProgram
    {
        return $value ? new TEALProgram($value) : null;
    }

    /**
     * Encode an array to a state schema.
     *
     * @param array|null $value
     * @return StateSchema|null
     */
    public static function toStateSchema(?array $value): ?StateSchema
    {
        if (is_null($value)) {
            return null;
        }

        return new StateSchema($value['nui'] ?? 0, $value['nbs'] ?? 0);
    }
}
