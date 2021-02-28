<?php


namespace Rootsoft\Algorand\Models\Teals;

/**
 * DryrunSource is TEAL source text that gets uploaded, compiled, and inserted into transactions or application state.
 *
 * Class DryrunSource
 * @package Rootsoft\Algorand\Models\Teals
 */
class DryrunSource
{
    /**
     * @var int
     */
    public int $appIndex;

    /**
     * FieldName is what kind of sources this is.
     * If lsig then it goes into the transactions[this.TxnIndex].
     * If approv or clearp it goes into the Approval Program or Clear State Program of application.
     * @var string
     */
    public string $fieldName;

    /**
     * @var string
     */
    public string $source;

    /**
     * @var string
     */
    public string $txnIndex;
}
