<?php

require_once '../../vendor/autoload.php';

use Rootsoft\Algorand\Algorand;
use Rootsoft\Algorand\Clients\AlgodClient;
use Rootsoft\Algorand\Clients\AlgoExplorer;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Models\Accounts\Address;

class AccountInformation
{
    public static function main()
    {
        $algodClient = new AlgodClient(AlgoExplorer::MAINNET_ALGOD_API_URL);
        $indexerClient = new IndexerClient(AlgoExplorer::MAINNET_INDEXER_API_URL);

        $algorand = new Algorand($algodClient, $indexerClient);

        $address = Address::fromAlgorandAddress('RDA5RAUX4N2SJ7KLXKFYBDFGBDZLB4SW3MBU3IQG5VCUIBASFID44NFH5I');

        self::getCreatedAssets($algorand, $address);

    }

    public static function getCreatedAssets(Algorand $algorand, Address $address)
    {
        $assets = $algorand->indexer()->getCreatedAssets($address->encodedAddress);
        return $assets->assets;
    }
}

AccountInformation::main();

function prettyPrint($data)
{
    echo $data . PHP_EOL;
}
