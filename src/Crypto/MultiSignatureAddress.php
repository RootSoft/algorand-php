<?php

namespace Rootsoft\Algorand\Crypto;

use MessagePack\Type\Bin;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\RawTransaction;
use Rootsoft\Algorand\Models\Transactions\SignedTransaction;
use Rootsoft\Algorand\Utils\CryptoUtils;
use Rootsoft\Algorand\Utils\MessagePackable;

/**
 * Multisignature accounts are a logical representation of an ordered set of addresses with a threshold and version.
 * Multisignature accounts can perform the same operations as other accounts, including sending transactions and
 * participating in consensus.
 *
 * The address for a multisignature account is essentially a hash of the ordered list of accounts, the threshold and
 * version values.
 * The threshold determines how many signatures are required to process any transaction from this multisignature
 * account.
 *
 * MultisigAddress is a convenience class for handling multisignature public identities.
 */
class MultiSignatureAddress implements MessagePackable
{
    /**
     * The prefix for a MultiSig Address.
     */
    public const MULTISIG_PREFIX = 'MultisigAddr';

    private int $version;

    private int $threshold;

    /**
     * @var array|Ed25519PublicKey[]
     */
    private array $publicKeys;

    /**
     * @param int $version
     * @param int $threshold
     * @param array|Ed25519PublicKey[] $publicKeys
     */
    public function __construct(int $version, int $threshold, array $publicKeys)
    {
        if ($version != 1) {
            throw new AlgorandException('Unknown msig version');
        }

        if ($threshold == 0 || empty($publicKeys) || $threshold > count($publicKeys)) {
            throw new AlgorandException('Invalid threshold');
        }

        $this->version = $version;
        $this->threshold = $threshold;
        $this->publicKeys = $publicKeys;
    }

    /**
     * Create and sign a multisig transaction from the input and the multisig account.
     *
     * @param Account $account
     * @param RawTransaction $transaction
     * @return SignedTransaction
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public function sign(Account $account, RawTransaction $transaction) : SignedTransaction
    {
        $sender = $transaction->sender;
        if (is_null($sender)) {
            throw new AlgorandException('Sender is not valid');
        }

        // Check that from addr of tx matches multisig preimage
        if ($sender->encodedAddress != self::toString()) {
            throw new AlgorandException('Transaction sender does not match multisig account');
        }

        // check that account secret key is in multisig pk list
        $publicKey = new Ed25519PublicKey($account->getPublicKey());

        $index = array_search($publicKey, $this->publicKeys);
        if ($index === false) {
            throw new AlgorandException('Multisig account does not contain this secret key');
        }

        $signedTx = $transaction->sign($account);
        $subsigs = [];
        for ($i = 0; $i < count($this->publicKeys); $i++) {
            if ($i == $index) {
                $subsigs[] = new MultisigSubsig($publicKey, $signedTx->getSignature());
            } else {
                $subsigs[] = new MultisigSubsig($this->publicKeys[$i]);
            }
        }

        $msig = new MultiSignature($this->version, $this->threshold, $subsigs);

        return SignedTransaction::fromMultiSignature($transaction, $msig);
    }

    /**
     * Appends our signature to the given multisig transaction.
     * Transaction is the partially signed msig tx to which to append signature.
     *
     * @param Account $account
     * @param SignedTransaction $transaction
     * @return SignedTransaction Returns a merged multisig transaction.
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public function append(Account $account, SignedTransaction $transaction) : SignedTransaction
    {
        $signedTx = $this->sign($account, $transaction->getTransaction());

        return self::mergeMultisigTransactions([$signedTx, $transaction]);
    }

    /**
     * Merges the given (partially) signed multisig transactions.
     * Transactions are the partially signed multisig transactions to merge.
     * Underlying transactions may be mutated.
     *
     * @param array|SignedTransaction[] $transactions
     * @return SignedTransaction the merged multisig transaction
     * @throws AlgorandException
     */
    public static function mergeMultisigTransactions(array $transactions) : SignedTransaction
    {
        if (count($transactions) < 2) {
            throw new AlgorandException('Cannot merge a single transaction');
        }

        $merged = $transactions[0];

        for ($i = 0; $i < count($transactions); $i++) {
            $tx = $transactions[$i];
            $msig = $tx->getMultiSignature();
            if (is_null($msig)) {
                throw new AlgorandException('No valid multisignature');
            }

            if ($msig->getVersion() != $merged->getMultiSignature()->getVersion()
                || $msig->getThreshold() != $merged->getMultiSignature()->getThreshold()) {
                throw new AlgorandException('transaction msig parameters do not match');
            }

            for ($j = 0; $j < count($msig->getSubsigs()); $j++) {
                $myMsig = $merged->getMultiSignature()->getSubsigs()[$j];
                $theirMsig = $msig->getSubsigs()[$j];
                if (is_null($myMsig)) {
                    throw new AlgorandException('No valid subsig');
                }

                if ($theirMsig->getPublicKey() != $myMsig->getPublicKey()) {
                    throw new AlgorandException('transaction msig public keys do not match');
                }

                if ($myMsig->getSignature() == null) {
                    $myMsig->setSignature($theirMsig->getSignature());
                } elseif ($myMsig->getSignature() != $theirMsig->getSignature() && $theirMsig->getSignature() != null) {
                    throw new AlgorandException('transaction msig has mismatched signatures');
                }

                $merged->getMultiSignature()->getSubsigs()[$j] = $myMsig;
            }
        }

        return $merged;
    }

    /**
     * @return int
     */
    public function getVersion(): int
    {
        return $this->version;
    }

    /**
     * @return int
     */
    public function getThreshold(): int
    {
        return $this->threshold;
    }

    /**
     * @return array|Ed25519PublicKey[]
     */
    public function getPublicKeys(): array
    {
        return $this->publicKeys;
    }

    /**
     * Return the address for this multisig address.
     *
     * @return Address
     * @throws \SodiumException
     */
    public function toAddress() : Address
    {
        $address = '';
        $address .= utf8_encode(self::MULTISIG_PREFIX);
        $address .= pack('C', $this->version);
        $address .= pack('C', $this->threshold);
        foreach ($this->publicKeys as $publicKey) {
            $address .= $publicKey->bytes();
        }

        $digest = CryptoUtils::sha512256($address);

        return Address::fromPublicKey($digest);
    }

    public function toString() : string
    {
        return $this->toAddress()->encodedAddress;
    }

    public function toMessagePack(): array
    {
        return [
            'version' => $this->version,
            'threshold' => $this->threshold,
            'publicKeys' => array_map(fn ($value) => new Bin($value->bytes()), $this->publicKeys),
        ];
    }
}
