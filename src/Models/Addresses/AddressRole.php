<?php


namespace Rootsoft\Algorand\Models\Addresses;

use MyCLabs\Enum\Enum;

/**
 *
 * Class AddressRole
 * @package Rootsoft\Algorand\Models\Addresses
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
        return new AddressRole(self::SENDER);
    }

    /**
     * @return AddressRole
     */
    public static function RECEIVER()
    {
        return new AddressRole(self::RECEIVER);
    }

    /**
     * @return AddressRole
     */
    public static function FREEZETARGET()
    {
        return new AddressRole(self::FREEZETARGET);
    }
}
