<?php

namespace Rootsoft\Algorand\Crypto;

use Exception;
use MessagePack\Type\Bin;
use ParagonIE\ConstantTime\Base64;
use ParagonIE\Halite\Asymmetric\SignatureSecretKey;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Applications\TEALProgram;
use Rootsoft\Algorand\Models\Transactions\RawTransaction;
use Rootsoft\Algorand\Models\Transactions\SignedTransaction;
use Rootsoft\Algorand\Utils\AlgorandUtils;
use Rootsoft\Algorand\Utils\CryptoUtils;
use Rootsoft\Algorand\Utils\Encoder;
use Rootsoft\Algorand\Utils\MessagePackable;

/**
 * Most Algorand transactions are authorized by a signature from a single account or a multisignature account.
 *
 * Algorand’s stateful smart contracts allow for a third type of signature using a
 * Transaction Execution Approval Language (TEAL) program, called a logic signature (LogicSig).
 *
 * Stateless smart contracts provide two modes for TEAL logic to operate as a LogicSig, to create a contract account
 * that functions similar to an escrow or to delegate signature authority to another account.
 *
 * More information, see
 * https://developer.algorand.org/docs/features/asc1/stateless/sdks/
 */
class LogicSignature implements MessagePackable
{
    /**
     * The prefix for a program.
     */
    public const LOGIC_PREFIX = 'Program';

    private string $logic;

    /**
     * @var string[]
     */
    private ?array $arguments;

    private ?Signature $signature;

    private ?MultiSignature $multiSignature;

    /**
     * Create a new logic signature.
     *
     * @param string $logic
     * @param string[]|null $arguments
     * @param Signature|null $signature
     * @param MultiSignature|null $multiSignature
     */
    public function __construct(
        string $logic,
        ?array $arguments = null,
        ?Signature $signature = null,
        ?MultiSignature $multiSignature = null
    ) {
        $this->logic = $logic;
        $this->arguments = $arguments;
        $this->signature = $signature;
        $this->multiSignature = $multiSignature;

        // Validate program/logic
        Logic::checkProgram($logic, $arguments);
    }

    /**
     * Create a new logic signature from a given TEAL program.
     * Throws an AlgorandException if unable to check the logic.
     *
     * @param TEALProgram $program
     * @param array|null $arguments
     * @param Signature|null $signature
     * @param MultiSignature|null $multiSignature
     * @return LogicSignature
     */
    public static function fromProgram(
        TEALProgram $program,
        ?array $arguments = null,
        ?Signature $signature = null,
        ?MultiSignature $multiSignature = null
    ) {
        $instance = new self($program->bytes(), $arguments, $signature, $multiSignature);

        return $instance;
    }

    /**
     * Perform signature verification against the sender address.
     *
     * @param Address $address
     * @return bool
     * @throws \SodiumException
     */
    public function verify(Address $address): bool
    {
        if ($this->signature != null && $this->multiSignature != null) {
            return false;
        }

        try {
            Logic::checkProgram($this->logic, $this->arguments);
        } catch (Exception $ex) {
            return false;
        }

        if ($this->signature == null && $this->multiSignature == null) {
            try {
                return $address == $this->toAddress();
            } catch (Exception $ex) {
                return false;
            }
        }

        // Verify signature
        if ($this->signature != null) {
            $verified = CryptoUtils::verify($this->getEncodedProgram(), $this->signature->bytes(), $address->address);

            return $verified;
        }

        return $this->multiSignature->verify($this->getEncodedProgram());
    }

    /**
     * Sign a logic signature with account secret key.
     *
     * @param Account $account
     * @param MultiSignatureAddress|null $multiSignatureAddress
     * @return LogicSignature
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public function sign(Account $account, ?MultiSignatureAddress $multiSignatureAddress = null): self
    {
        if ($multiSignatureAddress != null) {
            return $this->signLogicSigAsMultiSig($account, $multiSignatureAddress);
        }

        $secretKey = $account->getPrivateKeyPair()->getSecretKey();
        if (! ($secretKey instanceof SignatureSecretKey)) {
            throw new AlgorandException('Private key is not a valid signing key.');
        }

        // Get the encoded program
        $encodedProgram = $this->getEncodedProgram();

        // Sign the transaction with secret key
        $signature = CryptoUtils::sign($encodedProgram, $secretKey);

        $this->signature = new Signature($signature);

        return $this;
    }

    /**
     * Sign this logic signature with the the multi sig address.
     *
     * @param Account $account
     * @param MultiSignatureAddress $address
     * @return LogicSignature
     * @throws AlgorandException
     * @throws \SodiumException
     */
    private function signLogicSigAsMultiSig(Account $account, MultiSignatureAddress $address) : self
    {
        $publicKey = new Ed25519PublicKey($account->getPublicKey());
        $index = array_search($publicKey, $address->getPublicKeys());
        if ($index === false) {
            throw new AlgorandException('Multisig account does not contain this secret key');
        }

        // Sign the program
        $signature = $account->sign($this->getEncodedProgram());

        // Create the multi signature
        $msig = new MultiSignature($address->getVersion(), $address->getThreshold(), []);

        for ($i = 0; $i < count($address->getPublicKeys()); $i++) {
            if ($i == $index) {
                $msig->addSubsig(new MultisigSubsig($publicKey, $signature));
            } else {
                $msig->addSubsig(new MultisigSubsig($address->getPublicKeys()[$i]));
            }
        }

        $lsig = clone $this;
        $lsig->multiSignature = $msig;

        return $lsig;
    }

    /**
     * Generate escrow address from logic sig program.
     * @return Address The address for the encoded program.
     * @throws \SodiumException
     */
    public function toAddress(): Address
    {
        $encodedProgram = $this->getEncodedProgram();
        $digest = AlgorandUtils::hash($encodedProgram);

        return Address::fromPublicKey($digest);
    }

    /**
     * Get the encoded representation of the program with a prefix suitable for signing.
     * @return string
     */
    public function getEncodedProgram(): String
    {
        // Prepend the transaction prefix
        $txBytes = utf8_encode(self::LOGIC_PREFIX);
        $encodedProgram = $txBytes.$this->logic;

        return $encodedProgram;
    }

    /**
     * Get the logic program, in bytes.
     * @return string
     */
    public function getLogic(): string
    {
        return $this->logic;
    }

    /**
     * Get the arguments.
     *
     * @return string[]
     */
    public function getArguments(): ?array
    {
        return $this->arguments;
    }

    /**
     * Get the signature of this LogicSignature.
     *
     * @return Signature|null
     */
    public function getSignature(): ?Signature
    {
        return $this->signature;
    }

    /**
     * @return MultiSignature|null
     */
    public function getMultiSignature(): ?MultiSignature
    {
        return $this->multiSignature;
    }

    /**
     * Create a signed transaction from a LogicSignature and transaction.
     * LogicSignature must be valid and verifiable against transaction sender field.
     *
     * @param RawTransaction $transaction
     * @return SignedTransaction a signed transaction from a given logic signature.
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public function signTransaction(RawTransaction $transaction) : SignedTransaction
    {
        $sender = $transaction->sender;
        if (is_null($sender)) {
            throw new AlgorandException('No sender specified');
        }

        // Verify lsig
        $verified = $this->verify($sender);
        if (! $verified) {
            throw new AlgorandException('Verification failed');
        }

        // Create signed transaction with lsig
        return SignedTransaction::fromLogicSignature($transaction, $this);
    }

    /**
     * Appends a signature to multisig logic signed transaction.
     *
     * @param Account $account
     * @return LogicSignature
     * @throws AlgorandException
     * @throws \SodiumException
     */
    public function append(Account $account) : self
    {
        $msig = $this->getMultiSignature();
        if (is_null($msig)) {
            throw new AlgorandException('The logicsig has no valid multisig');
        }

        $publicKey = new Ed25519PublicKey($account->getPublicKey());
        $keys = array_map(fn ($subsig) => $subsig->getPublicKey(), $msig->getSubsigs());
        $index = array_search($publicKey, $keys);

        if ($index === false) {
            throw new AlgorandException('Multisig account does not contain this secret key');
        }

        $signature = $account->sign($this->getEncodedProgram());
        $msig->updateSubsig($index, new MultisigSubsig($publicKey, $signature));

        $lsig = clone $this;
        $lsig->multiSignature = $msig;

        return $lsig;
    }

    public function toMessagePack(): array
    {
        return [
            'l' => $this->logic ? new Bin($this->logic) : null,
            'arg' => ! empty($this->arguments) ? array_map(fn ($value) => new Bin($value), $this->arguments) : null,
            'sig' => $this->signature ? new Bin($this->signature->bytes()) : null,
            'msig' => $this->multiSignature,
        ];
    }

    /**
     * Get the base64-encoded representation of this logic signature.
     *
     * @return string
     */
    public function toBase64(): string
    {
        return Base64::encode(Encoder::getInstance()->encodeMessagePack($this->toMessagePack()));
    }
}
