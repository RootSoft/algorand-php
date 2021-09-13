<?php

namespace Rootsoft\Algorand\Models;

/**
 * Describes the parameters used by this account in consensus protocol.
 *
 * Class AccountParticipation
 */
class AccountParticipation
{
    /**
     * Selection public key (if any) currently registered for this round.
     * @var string
     * @required
     */
    public string $selectionParticipationKey;

    /**
     * First round for which this participation is valid.
     * @var int
     * @required
     */
    public int $voteFirstValid;

    /**
     * Last round for which this participation is valid.
     * @var int
     * @required
     */
    public int $voteLastValid;

    /**
     * Number of subkeys in each batch of participation keys.
     * @var int
     * @required
     */
    public int $voteKeyDilution;

    /**
     * Root participation public key (if any) currently registered for this round.
     * @var string
     * @required
     */
    public string $voteParticipationKey;
}
