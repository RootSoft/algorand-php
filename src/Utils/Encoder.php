<?php

namespace Rootsoft\Algorand\Utils;

use MessagePack\BufferUnpacker;
use MessagePack\Packer;
use MessagePack\TypeTransformer\BinTransformer;
use Rootsoft\Algorand\Utils\Transformers\ApplicationCreateTransformer;
use Rootsoft\Algorand\Utils\Transformers\ApplicationTransformer;
use Rootsoft\Algorand\Utils\Transformers\ApplicationUpdateTransformer;
use Rootsoft\Algorand\Utils\Transformers\AssetConfigTransformer;
use Rootsoft\Algorand\Utils\Transformers\AssetFreezeTransformer;
use Rootsoft\Algorand\Utils\Transformers\AssetTransferTransformer;
use Rootsoft\Algorand\Utils\Transformers\BaseTransactionTransformer;
use Rootsoft\Algorand\Utils\Transformers\KeyRegistrationTransformer;
use Rootsoft\Algorand\Utils\Transformers\LogicSignatureTransformer;
use Rootsoft\Algorand\Utils\Transformers\PaymentTransactionTransformer;
use Rootsoft\Algorand\Utils\Transformers\SignedTransactionTransformer;
use Rootsoft\Algorand\Utils\Transformers\TransformerFactory;

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

    public BufferUnpacker $unpacker;

    public TransformerFactory $transformerFactory;

    private function __construct()
    {
        $this->packer = new Packer(null, [new BinTransformer(), new SignedTransactionTransformer()]);
        $this->unpacker = new BufferUnpacker();
        $this->transformerFactory = new TransformerFactory($this->unpacker);
        $this->transformerFactory->registerTransformer(new BaseTransactionTransformer());
        $this->transformerFactory->registerTransformer(new SignedTransactionTransformer($this->transformerFactory));
        $this->transformerFactory->registerTransformer(new PaymentTransactionTransformer());
        $this->transformerFactory->registerTransformer(new AssetConfigTransformer());
        $this->transformerFactory->registerTransformer(new AssetTransferTransformer());
        $this->transformerFactory->registerTransformer(new AssetFreezeTransformer());
        $this->transformerFactory->registerTransformer(new KeyRegistrationTransformer());

        $this->transformerFactory->registerTransformer(new ApplicationTransformer());
        $this->transformerFactory->registerTransformer(new ApplicationUpdateTransformer());
        $this->transformerFactory->registerTransformer(new ApplicationCreateTransformer());

        $this->transformerFactory->registerTransformer(new LogicSignatureTransformer());
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

    /**
     * Decode a binary MessagePack string into the class object.
     * @param string $data
     * @param string $class
     * @return mixed
     */
    public function decodeMessagePack(string $data, string $className)
    {
        $data = $this->transformerFactory->transform($data, $className);

        return $data;
    }

    public function packer()
    {
        return $this->packer;
    }
}
