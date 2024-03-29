<?php

namespace Rootsoft\Algorand\Models\Transactions;

/**
 * Validation signature associated with some data. Only one of the signatures should be provided.
 *
 * Class TransactionSignature
 */
class TransactionSignature
{
    /**
     * Standard ed25519 signature.
     * @var string|null
     */
    public ?string $sig = null;
}
