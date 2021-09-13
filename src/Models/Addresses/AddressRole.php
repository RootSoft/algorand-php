<?php

namespace Rootsoft\Algorand\Models\Addresses;

use MyCLabs\Enum\Enum;

/**
 * Class AddressRole.
 */
final class AddressRole extends Enum
{
    private const SENDER = 'sender';

    private const RECEIVER = 'receiver';

    private const FREEZETARGET = 'freeze-target';

    /**
     * @return AddressRole
     */
    public static function SENDER()
    {
        return new self(self::SENDER);
    }

    /**
     * @return AddressRole
     */
    public static function RECEIVER()
    {
        return new self(self::RECEIVER);
    }

    /**
     * @return AddressRole
     */
    public static function FREEZETARGET()
    {
        return new self(self::FREEZETARGET);
    }
}
