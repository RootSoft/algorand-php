<?php

namespace Rootsoft\Algorand\Models\Transactions;

class KeyRegistrationTransactionResponse
{
    /**
     * Mark the account as participating or non-participating.
     * @var bool|null
     */
    public ?bool $nonParticipation = null;

    /**
     * Public key used with the Verified Random Function (VRF) result during committee selection.
     * @var string|null
     */
    public ?string $selectionParticipationKey = null;

    /**
     * First round this participation key is valid.
     * @var int|null
     */
    public ?int $voteFirstValid = null;

    /**
     * Last round this participation key is valid.
     * @var int|null
     */
    public ?int $voteLastValid = null;

    /**
     * Number of subkeys in each batch of participation keys.
     * @var int|null
     */
    public ?int $voteKeyDilution = null;

    /**
     * Participation public key used in key registration transactions.
     * @var string|null
     */
    public ?string $voteParticipationKey = null;
}
