<?php

namespace Rootsoft\Algorand\Models\Transactions;

use MyCLabs\Enum\Enum;

/**
 * @method static TransactionType RAW()
 * @method static TransactionType PAYMENT()
 * @method static TransactionType KEY_REGISTRATION()
 * @method static TransactionType ASSET_CONFIG()
 * @method static TransactionType ASSET_TRANSFER()
 * @method static TransactionType ASSET_FREEZE()
 * @method static TransactionType APPLICATION_CALL()
 */
final class TransactionType extends Enum
{
    private const RAW = '';

    private const PAYMENT = 'pay';

    private const KEY_REGISTRATION = 'keyreg';

    private const ASSET_CONFIG = 'acfg';

    private const ASSET_TRANSFER = 'axfer';

    private const ASSET_FREEZE = 'afrz';

    private const APPLICATION_CALL = 'appl';
}
