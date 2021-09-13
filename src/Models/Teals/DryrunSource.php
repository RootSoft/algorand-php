<?php

namespace Rootsoft\Algorand\Models\Teals;

/**
 * DryrunSource is TEAL source text that gets uploaded, compiled, and inserted into transactions or application state.
 *
 * Class DryrunSource
 */
class DryrunSource
{
    /**
     * @var int|null
     */
    public ?int $appIndex = null;

    /**
     * FieldName is what kind of sources this is.
     * If lsig then it goes into the transactions[this.TxnIndex].
     * If approv or clearp it goes into the Approval Program or Clear State Program of application.
     * @var string|null
     */
    public ?string $fieldName = null;

    /**
     * @var string|null
     */
    public ?string $source = null;

    /**
     * @var int|null
     */
    public ?int $txnIndex = null;
}
