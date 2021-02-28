<?php


namespace Rootsoft\Algorand\Models\Transactions;

/**
 * TransactionParams contains the parameters that help a client construct a new transaction.
 *
 * Class TransactionParams
 * @package Rootsoft\Algorand\Models\Transactions
 */
class TransactionParams
{
    /**
     * ConsensusVersion indicates the consensus protocol version as of LastRound.
     *
     * @var string
     * @required
     */
    public string $consensusVersion;

    /**
     * Fee is the suggested transaction fee
     * Fee is in units of micro-Algos per byte.
     * Fee may fall to zero but transactions must still have a fee of
     * at least MinTxnFee for the current network protocol.
     *
     * todo Uint64
     * @var int
     * @required
     */
    public int $fee;

    /**
     * GenesisID is an ID listed in the genesis block.
     *
     * @var string
     * @required
     */
    public string $genesisId;

    /**
     * GenesisHash is the hash of the genesis block.
     *
     * @var string
     * @required
     */
    public string $genesisHash;

    /**
     * LastRound indicates the last round seen
     *
     * todo Uint64
     * @var int
     * @required
     */
    public int $lastRound;

    /**
     * The minimum transaction fee (not per byte) required for the txn to validate for the current network protocol.
     *
     * todo Uint64
     * @var int
     * @required
     */
    public int $minFee;
}
