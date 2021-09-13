<?php

namespace Rootsoft\Algorand\Utils;

use Brick\Math\BigInteger;
use Brick\Math\BigNumber;
use Rootsoft\Algorand\Models\Transactions\RawTransaction;

class FeeCalculator
{
    /**
     * Calculate the total fee for a transaction.
     * This value is multiplied by the estimated size of the transaction in bytes to determine the total transaction fee.
     * If the result is less than the minimum fee, the minimum fee is used instead.
     *
     * @param RawTransaction $transaction
     * @param BigInteger $suggestedFeePerByte
     * @return BigInteger|BigNumber
     */
    public static function calculateFeePerByte(RawTransaction $transaction, BigInteger $suggestedFeePerByte) : BigInteger
    {
        return AlgorandUtils::calculate_fee_per_byte($transaction, $suggestedFeePerByte);
    }
}
