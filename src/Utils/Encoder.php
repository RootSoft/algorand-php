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

    public function encodeMessagePack(array $data)
    {
        return $this->packer->pack($data);
    }

    public function packer()
    {
        return $this->packer;
    }
}
