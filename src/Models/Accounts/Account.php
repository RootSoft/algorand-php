<?php


namespace Rootsoft\Algorand\Models\Accounts;

use Exception;
use ParagonIE\Halite\Alerts\InvalidKey;
use ParagonIE\Halite\Asymmetric\SignatureSecretKey;
use ParagonIE\Halite\HiddenString;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\KeyPair;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Exceptions\MnemonicException;
use Rootsoft\Algorand\Exceptions\WordListException;
use Rootsoft\Algorand\Mnemonic\Mnemonic;
use Rootsoft\Algorand\Mnemonic\SeedPhrase;
use Rootsoft\Algorand\Mnemonic\WordList;
use Rootsoft\Algorand\Traits\KeyPairGenerator;
use SodiumException;

/**
 * Accounts are entities on the Algorand blockchain associated with specific onchain data, like a balance.
 * An Algorand Address is the identifier for an Algorand account.
 *
 * After generating a private key and corresponding address,
 * sending Algos to the address on Algorand will initialize its state on the Algorand blockchain.
 *
 * Class Account
 * @package Rootsoft\Algorand\Models\Accounts
 */
class Account
{
    private KeyPair $privateKeyPair;

    private Address $address;

    /**
     * Account constructor.
     *
     * @param KeyPair $keyPair
     * @throws SodiumException
     */
    public function __construct(KeyPair $keyPair)
    {
        // Create and initialize EdDSA context
        $this->privateKeyPair = $keyPair;

        // Create a new address from the public key.
        $this->address = Address::fromPublicKey($this->privateKeyPair->getPublicKey()->getRawKeyMaterial());
    }

    /**
     * Create a new, random generated account.
     *
     * @return Account
     * @throws SodiumException
     * @throws InvalidKey
     */
    public static function random()
    {
        // Generate cryptographically secure pseudo-random key pair.
        $keyPair = KeyFactory::generateSignatureKeyPair();

        return new self($keyPair);
    }

    /**
     * Create an account from the given private key.
     *
     * @param SignatureSecretKey $secretKey
     * @return Account
     * @throws InvalidKey
     * @throws SodiumException
     */
    public static function fromSecretKey(string $secretKey)
    {
        $signatureSecretKey = new SignatureSecretKey(new HiddenString(hex2bin($secretKey)));

        // Derive seed from our secret key.
        $seed = KeyPairGenerator::deriveSeedFromSignatureSecretKey($signatureSecretKey);

        return self::seed($seed);
    }

    /**
     * Create an account from an rfc8037 private key
     *
     * @param string $seed
     * @return Account
     * @throws SodiumException
     * @throws InvalidKey
     */
    public static function seed(string $seed)
    {
        // Derive our keypair from the seed.
        $keyPair = KeyPairGenerator::deriveSignatureKeyPairFromSeed($seed);
        $instance = new self($keyPair);

        return $instance;
    }

    /**
     * Restore an account from the given seed phrase/mnemonic.
     *
     * @param $seedPhrase
     * @return Account
     * @throws InvalidKey
     * @throws MnemonicException
     * @throws SodiumException
     * @throws WordListException
     */
    public static function mnemonic($seedPhrase)
    {
        // Find the seed for the mnemonic
        $mnemonic = Mnemonic::Words($seedPhrase, WordList::English());
        $seed = hex2bin($mnemonic->entropy);

        // Derive our keypair from the seed.
        $keyPair = KeyPairGenerator::deriveSignatureKeyPairFromSeed($seed);
        $instance = new self($keyPair);

        return $instance;
    }

    /**
     * Export the secret key.
     *
     * This will take the seed from the secret and save it to a file.
     *
     * @param string $fileName
     * @return false|int
     */
    public function exportSecretKey(string $fileName)
    {
        // Derive the seed from the secret key
        $seed = KeyPairGenerator::deriveSeedFromSignatureSecretKey($this->getPrivateKeyPair()->getSecretKey());

        return file_put_contents($fileName, $seed);
    }

    /**
     * Get the public, human readable address of the account.
     * @return String
     */
    public function getPublicAddress()
    {
        return $this->address->encodedAddress;
    }

    /**
     * The Algorand address associated with this account.
     * @return Address
     */
    public function getAddress(): Address
    {
        return $this->address;
    }

    /**
     * @return KeyPair
     */
    public function getPrivateKeyPair(): KeyPair
    {
        return $this->privateKeyPair;
    }

    /**
     * Converts the private 32-byte key into a 25 word mnemonic.
     * The generated mnemonic includes a checksum.
     * Each word in the mnemonic represents 11 bits of data, and the last 11 bits are reserved for the checksum.
     *
     * https://developer.algorand.org/docs/features/accounts/#transformation-private-key-to-25-word-mnemonic
     *
     * @return SeedPhrase
     * @throws MnemonicException
     * @throws WordListException
     */
    public function getSeedPhrase(): SeedPhrase
    {
        // Fetch the seed from our secret key
        $seed = KeyPairGenerator::deriveSeedFromSignatureSecretKey($this->privateKeyPair->getSecretKey());

        // Generate mnemonic from our seed (32 bytes)
        return Mnemonic::Entropy(bin2hex($seed));
    }

}
