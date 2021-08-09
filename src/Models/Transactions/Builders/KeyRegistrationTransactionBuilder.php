<?php


namespace Rootsoft\Algorand\Models\Transactions\Builders;

use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Keys\ParticipationPublicKey;
use Rootsoft\Algorand\Models\Keys\VRFPublicKey;
use Rootsoft\Algorand\Models\Transactions\TransactionType;
use Rootsoft\Algorand\Models\Transactions\Types\KeyRegistrationTransaction;

class KeyRegistrationTransactionBuilder extends RawTransactionBuilder
{
    protected KeyRegistrationTransaction $keyRegTransacion;

    /**
     * KeyRegistrationTransactionBuilder constructor.
     */
    public function __construct()
    {
        $this->keyRegTransacion = new KeyRegistrationTransaction();
        parent::__construct(TransactionType::KEY_REGISTRATION(), $this->keyRegTransacion);
    }

    /**
     * The root participation public key
     *
     * @param ParticipationPublicKey|null $votePublicKey
     * @return $this
     */
    public function votePublicKey(?ParticipationPublicKey $votePublicKey)
    {
        $this->keyRegTransacion->votePK = $votePublicKey;

        return $this;
    }

    /**
     * The VRF public key.
     *
     * @param VRFPublicKey|null $selectionPublicKey
     * @return $this
     */
    public function selectionPublicKey(?VRFPublicKey $selectionPublicKey)
    {
        $this->keyRegTransacion->selectionPK = $selectionPublicKey;

        return $this;
    }

    /**
     * The first round that the participation key is valid.
     * Not to be confused with the FirstValid round of the keyreg transaction.
     *
     * @param int|null $voteFirst
     * @return $this
     */
    public function voteFirst(?int $voteFirst)
    {
        $this->keyRegTransacion->voteFirst = $voteFirst;

        return $this;
    }

    /**
     * The last round that the participation key is valid.
     * Not to be confused with the LastValid round of the keyreg transaction.
     *
     * @param int|null $voteLast
     * @return $this
     */
    public function voteLast(?int $voteLast)
    {
        $this->keyRegTransacion->voteLast = $voteLast;

        return $this;
    }

    /**
     * This is the dilution for the 2-level participation key.
     *
     * @param int|null $dilution
     * @return $this
     */
    public function voteKeyDilution(?int $dilution)
    {
        $this->keyRegTransacion->voteKeyDilution = $dilution;

        return $this;
    }

    /**
     * @return KeyRegistrationTransaction
     * @throws AlgorandException
     */
    public function build()
    {
        parent::build();

        // TODO Fix the ugly builders
        return $this->keyRegTransacion;
    }
}
