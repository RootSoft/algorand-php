<?php


namespace Rootsoft\Algorand\Models\Transactions\Types;

use MessagePack\Type\Bin;
use Rootsoft\Algorand\Models\Keys\ParticipationPublicKey;
use Rootsoft\Algorand\Models\Keys\VRFPublicKey;
use Rootsoft\Algorand\Models\Transactions\RawTransaction;

class KeyRegistrationTransaction extends RawTransaction
{

    /**
     * The root participation public key
     *
     * @var ParticipationPublicKey|null
     */
    public ?ParticipationPublicKey $votePK = null;

    /**
     * The VRF public key.
     *
     * @var VRFPublicKey|null
     */
    public ?VRFPublicKey $selectionPK = null;

    /**
     * The first round that the participation key is valid.
     * Not to be confused with the FirstValid round of the keyreg transaction.
     *
     * @var int|null
     */
    public ?int $voteFirst = null;

    /**
     * The last round that the participation key is valid.
     * Not to be confused with the LastValid round of the keyreg transaction.
     *
     * @var int|null
     */
    public ?int $voteLast = null;

    /**
     * This is the dilution for the 2-level participation key.
     *
     * @var int|null
     */
    public ?int $voteKeyDilution = null;

    public function toMessagePack(): array
    {
        $fields = parent::toMessagePack();
        $fields['votekey'] = $this->votePK != null ? new Bin($this->votePK->getBytes()) : null;
        $fields['selkey'] = $this->selectionPK != null ? new Bin($this->selectionPK->getBytes()) : null;
        $fields['votefst'] = $this->voteFirst;
        $fields['votelst'] = $this->voteLast;
        $fields['votekd'] = $this->voteKeyDilution;

        return $fields;
    }

}
