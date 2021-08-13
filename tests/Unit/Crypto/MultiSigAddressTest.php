<?php


namespace Rootsoft\Algorand\Tests\Unit\Crypto;

use Brick\Math\BigInteger;
use Orchestra\Testbench\TestCase;
use ParagonIE\ConstantTime\Base64;
use Rootsoft\Algorand\Crypto\Ed25519PublicKey;
use Rootsoft\Algorand\Crypto\LogicSignature;
use Rootsoft\Algorand\Crypto\MultiSignatureAddress;
use Rootsoft\Algorand\Crypto\Signature;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\TransactionParams;

class MultiSigAddressTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

    }

    public function testMultiSigAddressToString()
    {
        $one = Address::fromAlgorandAddress('XMHLMNAVJIMAW2RHJXLXKKK4G3J3U6VONNO3BTAQYVDC3MHTGDP3J5OCRU');
        $two = Address::fromAlgorandAddress('HTNOX33OCQI2JCOLZ2IRM3BC2WZ6JUILSLEORBPFI6W7GU5Q4ZW6LINHLA');
        $three = Address::fromAlgorandAddress('E6JSNTY4PVCY3IRZ6XEDHEO6VIHCQ5KGXCIQKFQCMB2N6HXRY4IB43VSHI');

        $publicKeys = array_map(fn (Address $value) => new Ed25519PublicKey($value->address), [$one, $two, $three]);
        $msigAddr = new MultiSignatureAddress(1, 2, $publicKeys);

        $this->assertEquals($msigAddr->toString(), 'UCE2U2JC4O4ZR6W763GUQCG57HQCDZEUJY4J5I6VYY4HQZUJDF7AKZO5GM');
    }

    public function testSignTransactionMultiSig()
    {
        $seed1 = Base64::decode('jBtuijiJFPZZsbgAiLSjtgBXPk3YaYmt2EMoScaYDxc=');
        $seed2 = Base64::decode('Q8Hsu3/xvxm2H4TOOLler94q4TyuiZW+Uy+3T4jbSzI=');
        $seed3 = Base64::decode('6WX10LQ6BkItrzHS5CgfsZuKrqFGOLNdV8A4FQdpawE=');

        $account1 = Account::seed($seed1);
        $account2 = Account::seed($seed2);
        $account3 = Account::seed($seed3);

        $publicKeys = array_map(fn (Account $value) => new Ed25519PublicKey($value->getPublicKey()), [$account1, $account2, $account3]);
        $msigAddr = new MultiSignatureAddress(1, 2, $publicKeys);

        $params = new TransactionParams();
        $params->consensusVersion = 'https://github.com/algorandfoundation/specs/tree/65b4ab3266c52c56a0fa7d591754887d68faad0a';
        $params->fee = 0;
        $params->genesisId = 'testnet-v1.0';
        $params->genesisHash = 'SGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9';
        $params->lastRound = 15993578;
        $params->minFee = 1000;

        $transaction = TransactionBuilder::payment()
            ->sender($msigAddr->toAddress())
            ->note('MSA')
            ->amount(1000000)
            ->receiver($account3->getAddress())
            ->suggestedParams($params)
            ->build();
        dump($msigAddr->toAddress());

        $signedTx = $msigAddr->sign($account1, $transaction);
        $completeTx = $msigAddr->append($account2, $signedTx);
    }

}
