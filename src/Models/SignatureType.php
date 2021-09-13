<?php

namespace Rootsoft\Algorand\Models;

use MyCLabs\Enum\Enum;

/**
 * Class SignatureType.
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
        return new self(self::STANDARD());
    }

    /**
     * @return SignatureType
     */
    public static function MULTISIG()
    {
        return new self(self::MULTISIG());
    }

    /**
     * @return SignatureType
     */
    public static function LOGICSIG()
    {
        return new self(self::LOGICSIG());
    }
}
