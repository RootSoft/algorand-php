<?php


namespace Rootsoft\Algorand\Models;

use MyCLabs\Enum\Enum;

/**
 *
 * Class SignatureType
 * @package Rootsoft\Algorand\Models
 */
final class SignatureType extends Enum
{
    private const STANDARD = 'sig';
    private const MULTISIG = 'msig';
    private const LOGICSIG = 'lsig';

    /**
     * @return SignatureType
     */
    public static function STANDARD()
    {
        return new SignatureType(self::STANDARD());
    }

    /**
     * @return SignatureType
     */
    public static function MULTISIG()
    {
        return new SignatureType(self::MULTISIG());
    }

    /**
     * @return SignatureType
     */
    public static function LOGICSIG()
    {
        return new SignatureType(self::LOGICSIG());
    }
}
