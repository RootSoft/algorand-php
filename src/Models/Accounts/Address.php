<?php


namespace Rootsoft\Algorand\Models\Accounts;

use Exception;
use ParagonIE\ConstantTime\Base32;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use SodiumException;

class Address
{
    const PUBLIC_KEY_LENGTH = 32;

    const ALGORAND_ADDRESS_BYTE_LENGTH = 36;

    const ALGORAND_CHECKSUM_BYTE_LENGTH = 4;

    const ALGORAND_ADDRESS_LENGTH = 58;

    const ALGORAND_ZERO_ADDRESS_STRING = "AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAY5HFKQ";

    /**
     * The string representation of the address.
     * @var string
     */
    public string $address;

    /**
     * The encoded address.
     * @var string
     */
    public string $encodedAddress;

    /**
     * Address constructor.
     * @param string $publicKey
     * @throws SodiumException
     */
    public function __construct(string $publicKey)
    {
        // TODO Check if valid public key
        $this->address = $publicKey;
    }

    /**
     * Create a new Address from a given Public Key.
     *
     * @param string $publicKey
     * @return Address
     * @throws SodiumException
     */
    public static function fromPublicKey(string $publicKey)
    {
        $instance = new self($publicKey);
        $instance->encodedAddress = self::encodeAddress($publicKey);

        return $instance;
    }

    /**
     * Create a new Address from a given Algorand address.
     * This will decode the address.
     *
     * @param string $encodedAddress
     * @return Address
     * @throws SodiumException
     * @throws AlgorandException
     */
    public static function fromAlgorandAddress(string $encodedAddress)
    {
        $publicKey = self::decodeAddress($encodedAddress);

        $instance = new self($publicKey);
        $instance->encodedAddress = $encodedAddress;

        return $instance;
    }

    /**
     * Encode a public key to a human-readable representation, with
     * a 4-byte checksum appended at the end, using SHA256.
     *
     * Note that string representations of addresses generated by different SDKs may not be compatible.
     *
     * @param string $address
     * @return false|string
     */
    public static function encodeAddress(string $address)
    {
        // Compute the hash and base32 encode
        $hashedAddress = hash('sha512/256', $address, true);

        // Take the last 4 bytes and append to addr
        $checksum = substr($hashedAddress, -4);
        $encodedAddress = Base32::encodeUpperUnpadded($address . $checksum);

        return $encodedAddress;
    }

    /**
     * Decode an encoded, uppercased Algorand address to a public key.
     *
     * @param string $encodedAddress
     * @return string The public key
     * @throws AlgorandException
     */
    public static function decodeAddress(string $encodedAddress)
    {
        // Decode the address
        $checksumAddress = Base32::decodeUpper($encodedAddress);

        // Sanity check length
        if (strlen($checksumAddress) != self::PUBLIC_KEY_LENGTH + self::ALGORAND_CHECKSUM_BYTE_LENGTH) {
            throw new AlgorandException("Input string is an invalid address. Wrong length");
        }

        // Find public key & checksum
        $publicKey = substr($checksumAddress, 0, self::PUBLIC_KEY_LENGTH);
        $checksum = substr($checksumAddress, self::PUBLIC_KEY_LENGTH, self::ALGORAND_CHECKSUM_BYTE_LENGTH);

        if (! $publicKey) {
            throw new AlgorandException('Invalid Algorand address - unable to parse');
        }

        // Compute expected checksum
        $hashedAddress = hash('sha512/256', $publicKey, true);
        $expectedChecksum = substr($hashedAddress, self::PUBLIC_KEY_LENGTH - self::ALGORAND_CHECKSUM_BYTE_LENGTH);
        if ($checksum != $expectedChecksum) {
            throw new AlgorandException('Invalid Algorand address - checksums do not match.');
        }

        return $publicKey;
    }

    /**
     * Check if the given address is a valid Algorand address.
     *
     * @param string $address
     * @return bool
     */
    public static function isAlgorandAddress(string $address)
    {
        try {
            self::decodeAddress($address);

            return true;
        } catch (Exception $ex) {
            return false;
        }
    }
}
