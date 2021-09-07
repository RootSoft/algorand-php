<?php
require_once('../../vendor/autoload.php');

use Rootsoft\Algorand\Algorand;
use Rootsoft\Algorand\Clients\AlgodClient;
use Rootsoft\Algorand\Clients\AlgoExplorer;
use Rootsoft\Algorand\Clients\IndexerClient;

class CompileTEALExample
{
    public static function main()
    {
        $algodClient = new AlgodClient(AlgoExplorer::TESTNET_ALGOD_API_URL);
        $indexerClient = new IndexerClient(AlgoExplorer::TESTNET_INDEXER_API_URL);
        $algorand = new Algorand($algodClient, $indexerClient);

        // Compile TEAL
        prettyPrint('Compiling TEAL code');
        $result = $algorand->applicationManager()->compileTEAL(self::$sampleArgsTeal);

        prettyPrint('Hash: ' . $result->hash);
        prettyPrint('Result: ' . $result->result);
        prettyPrint('Program: ' . $result->program()->bytes());
    }

    private static $sampleArgsTeal = '// samplearg.teal
        // This code is meant for learning purposes only
        // It should not be used in production
        arg_0
        btoi
        int 123
        ==';
}

CompileTEALExample::main();

function prettyPrint($data)
{
    echo $data . PHP_EOL;
}
