<?php


namespace Rootsoft\Algorand\Services;

use Exception;
use ParagonIE\Halite\Alerts\InvalidKey;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\AccountInformation;
use SodiumException;

trait ManagesAccountsV2
{
    /**
     * Create a new, random generated account.
     *
     * @return Account
     * @throws Exception
     */
    public function createNewAccount()
    {
        return Account::random();
    }

    /**
     * Load an existing account from a private key.
     * The private key should be in hex.
     *
     * @param string $secretKey
     * @return Account
     * @throws InvalidKey
     * @throws SodiumException
     */
    public function loadAccountFromSecret(string $secretKey): Account
    {
        // Derive the seed from the secret key
        return Account::fromSecretKey($secretKey);
    }

    /**
     * Load an existing account from an rfc8037 private key.
     * The seed should be in binary.
     *
     * @param string $seed
     * @return Account
     * @throws Exception
     */
    public function loadAccountFromSeed(string $seed)
    {
        return Account::seed($seed);
    }

    /**
     * Restores an existing account from a mnemonic phrase.
     *
     * @param string|array $mnemonic
     * @return Account
     * @throws Exception
     */
    public function restoreAccount($mnemonic)
    {
        if (is_string($mnemonic)) {
            $mnemonic = explode(' ', $mnemonic);
        }

        return Account::mnemonic($mnemonic);
    }

    /**
     * Gets the account information.
     * Given a specific account public key, this call returns the accounts status, balance and spendable amounts.
     *
     * @param string $address
     * @return AccountInformation The account information at a given round.
     */
    public function getAccountInformation(string $address)
    {
        $response = $this->get($this->algodClient, "/v2/accounts/$address");

        return $this->jsonMapper->map($response, new AccountInformation());
    }

    /**
     * Generate (or renew) and register participation keys on the node for a given account address.
     *
     * @param string $address
     * @param array $params
     * @return mixed
     */
    public function generateParticipationKeys(string $address, array $params = [])
    {
        $response = $this->post($this->algodClient, "/v2/register-participation-keys/$address", $params);

        // TODO Implementation
        return $this->jsonMapper->map($response, new AccountInformation());
    }
}
