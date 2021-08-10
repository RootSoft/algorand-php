<?php


namespace Rootsoft\Algorand\Utils;

use MessagePack\Packer;
use MessagePack\TypeTransformer\BinTransformer;
use Rootsoft\Algorand\Utils\Transformers\SignedTransactionTransformer;

class Encoder
{
    /**
     * A singleton instance, handling the encoding.
     * @var
     */
    private static $instance;

    /**
     * The instance used to pack the msgpacks.
     * @var Packer
     */
    public Packer $packer;

    private function __construct()
    {
        $this->packer = new Packer(null, [new BinTransformer(), new SignedTransactionTransformer()]);
    }

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    //
    public function encodeMessagePack(array $data)
    {
        $sanitizedMap = $this->prepareMessagePack($data);

        return $this->packer->pack($this->prepareMessagePack($sanitizedMap));
    }

    public function prepareMessagePack(array $data): array
    {
        $sanitizedMap = [];

        // Sanitize and remove canonical values
        foreach ($data as $key => $value) {
            $v = $value;
            if (is_array($value)) {
                $v = $this->prepareMessagePack($value);
            } elseif ($value instanceof MessagePackable) {
                $v = $this->prepareMessagePack($value->toMessagePack());
            }

            $sanitizedMap[$key] = $v;
        }

        return AlgorandUtils::algorand_array_clean($sanitizedMap);
    }

    public function packer()
    {
        return $this->packer;
    }
}
