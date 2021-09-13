<?php

namespace Rootsoft\Algorand\Utils;

/**
 * TODO big int or validation.
 *
 * Class Algo
 */
class Algo
{
    public const MICROALGOS = 'microAlgos';

    public const ALGOS = 'Algos';

    /**
     * @var array
     */
    private static $map = [
        self::MICROALGOS => 1,
        self::ALGOS => 1000000,
    ];

    /**
     * Convert an amount of Algo's to the base unit of microAlgos.
     *
     * @param float $algo
     * @return int
     */
    public static function toMicroAlgos(float $algo)
    {
        return $algo * self::$map[self::ALGOS];
    }

    /**
     * Convert an amount of microAlgo's to Algos.
     *
     * @param float $microAlgos
     * @return int
     */
    public static function fromMicroAlgos(float $microAlgos)
    {
        return $microAlgos / self::$map[self::ALGOS];
    }
}
