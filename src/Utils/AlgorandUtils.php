<?php


namespace Rootsoft\Algorand\Utils;

use Brick\Math\BigInteger;
use Brick\Math\BigNumber;
use Exception;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Transactions\RawTransaction;

class AlgorandUtils
{
    /**
     * Generate the hash for the given data.
     * @param $data
     * @return string
     */
    public static function hash($data)
    {
        return hash('sha512/256', $data, true);
    }

    public static function algorand_array_clean(array $data)
    {
        // Sort keys alphabetically
        ksort($data);

        // Omit empty, 0 and false fields
        return array_filter($data, fn ($value) => ! is_null($value) && $value !== '' && $value !== false && $value !== 0);
    }

    /**
     * Calculate the total fee for a transaction.
     * This value is multiplied by the estimated size of the transaction in bytes to determine the total transaction fee.
     * If the result is less than the minimum fee, the minimum fee is used instead.
     *
     * @param RawTransaction $transaction
     * @param BigInteger $suggestedFeePerByte
     * @return BigInteger|BigNumber
     */
    public static function calculate_fee_per_byte(RawTransaction  $transaction, BigInteger $suggestedFeePerByte)
    {
        $transactionFee = $suggestedFeePerByte->multipliedBy(self::estimateTransactionSize($transaction));
        if ($transactionFee->compareTo(BigInteger::of(RawTransaction::MIN_TX_FEE_UALGOS)) < 0) {
            $transactionFee = BigInteger::of(RawTransaction::MIN_TX_FEE_UALGOS);
        }

        return $transactionFee;
    }

    /**
     * Returns the estimated encoded size of the transaction, including the signature.
     * This function is useful for calculating the fee from suggested fee per byte.
     *
     * @param RawTransaction $transaction
     * @return int an estimated byte size for the transaction.
     */
    public static function estimateTransactionSize(RawTransaction $transaction)
    {
        // Create a random account to sign the transaction
        try {
            $randomAccount = Account::random();

            // Sign the transaction
            $signedTransaction = $transaction->sign($randomAccount);

            // Encode the transaction
            return strlen(Encoder::getInstance()->encodeMessagePack($signedTransaction->toArray()));
        } catch (Exception $e) {
            // Unable to calculate the fee, so use the min fee.
            return 0;
        }
    }

    /**
     * Check if a given string contains the value.
     *
     * @param $input
     * @param $search
     * @return bool
     */
    public static function string_contains($input, $search)
    {
        return preg_match("/{$search}/i", $input);
    }

    public static function format_url(string $baseUrl)
    {
        return rtrim($baseUrl, '/') . '/';
    }
}
