<?php

namespace Rootsoft\Algorand\Tests\Unit\Accounts;

use Orchestra\Testbench\TestCase;
use ParagonIE\ConstantTime\Base64;
use Rootsoft\Algorand\Crypto\Ed25519PublicKey;
use Rootsoft\Algorand\Crypto\LogicSignature;
use Rootsoft\Algorand\Crypto\MultiSignatureAddress;
use Rootsoft\Algorand\Crypto\Signature;
use Rootsoft\Algorand\Mnemonic\Mnemonic;
use Rootsoft\Algorand\Mnemonic\WordList;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Applications\TEALProgram;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Utils\Buffer;
use Rootsoft\Algorand\Utils\CryptoUtils;
use Rootsoft\Algorand\Utils\Encoder;

class AccountTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testSignTransactionE2E()
    {
        $goldenTx = '82a3736967c4403f5a5cbc5cb038b0d29a53c0adf8a643822da0e41681bcab050e406fd40af20aa56a2f8c0e05d3bee8d4e8489ef13438151911b31b5ed5b660cac6bae4080507a374786e87a3616d74cd04d2a3666565cd03e8a26676ce0001a04fa26c76ce0001a437a3726376c4207d3f99e53d34ae49eb2f458761cf538408ffdaee35c70d8234166de7abe3e517a3736e64c4201bd63dc672b0bb29d42fcafa3422a4d385c0c8169bb01595babf8855cf596979a474797065a3706179';
        $goldenTxId = 'BXSNCHKYEXB4AQXFRROUJGZ4ZWD7WL2F5D27YUPFR7ONDK5TMN5Q';

        $sender = Address::fromAlgorandAddress('DPLD3RTSWC5STVBPZL5DIIVE2OC4BSAWTOYBLFN2X6EFLT2ZNF4SMX64UA');
        $receiver = Address::fromAlgorandAddress('PU7ZTZJ5GSXET2ZPIWDWDT2TQQEP7WXOGXDQ3ARUCZW6PK7D4ULSE6NYCE');
        $account = Account::mnemonic('actress tongue harbor tray suspect odor load topple vocal avoid ignore apple lunch unknown tissue museum once switch captain place lemon sail outdoor absent creek');

        $transaction = TransactionBuilder::payment()
            ->sender($sender)
            ->receiver($receiver)
            ->flatFee(1000)
            ->amount(1234)
            ->firstValid(106575)
            ->lastValid(107575)
            ->build();

        $this->assertEquals($account->getPublicAddress(), $sender->encodedAddress);

        // Sign the transaction
        $signedTx = $transaction->sign($account);
        $signedBytes = Encoder::getInstance()->encodeMessagePack($signedTx->toMessagePack());

        $this->assertEquals($signedTx->getTransaction()->getTransactionId(), $goldenTxId);
        $this->assertEquals(bin2hex($signedBytes), $goldenTx);
    }

    public function testKeygen()
    {
        for ($i = 0; $i < 1000; $i++) {
            $account = Account::random();
            $this->assertNotEmpty($account->getPublicKey());
            $this->assertNotNull($account->getAddress());
            $this->assertEquals($account->getPublicKey(), $account->getAddress()->address);
        }
    }

    public function testToMnemonic()
    {
        $words = 'actress tongue harbor tray suspect odor load topple vocal avoid ignore apple lunch unknown tissue museum once switch captain place lemon sail outdoor absent creek';
        $account = Account::mnemonic($words);
        $seedphrase = $account->getSeedPhrase();
        $this->assertEquals($seedphrase->words, explode(' ', $words));
    }

    public function testSignMsigTransaction()
    {
        $goldenTx = 'gqRtc2lng6ZzdWJzaWeTgqJwa8QgG37AsEvqYbeWkJfmy/QH4QinBTUdC8mKvrEiCairgXihc8RAdvZ3y9GsInBPutdwKc7Jy+an13CcjSV1lcvRAYQKYOxXwfgT5B/mK14R57ueYJTYyoDO8zBY6kQmBalWkm95AIGicGvEIAljMglTc4nwdWcRdzmRx9A+G3PIxPUr9q/wGqJc+cJxgaJwa8Qg5/D4TQaBHfnzHI2HixFV9GcdUaGFwgCQhmf0SVhwaKGjdGhyAqF2AaN0eG6Jo2FtdM0TiKNmZWXOAANPqKJmds4ADtbco2dlbq10ZXN0bmV0LXYzMS4womx2zgAO2sSkbm90ZcQItFF5Ofz60nGjcmN2xCAbfsCwS+pht5aQl+bL9AfhCKcFNR0LyYq+sSIJqKuBeKNzbmTEII2StImQAXOgTfpDWaNmamr86ixCoF3Zwfc+66VHgDfppHR5cGWjcGF5';
        $goldenTxId = 'KY6I7NQXQDAMDUCAVATI45BAODW6NRYQKFH4KIHLH2HQI4DO4XBA';

        $msa = $this->createTestMsigAddress();
        $receiver = Address::fromAlgorandAddress('DN7MBMCL5JQ3PFUQS7TMX5AH4EEKOBJVDUF4TCV6WERATKFLQF4MQUPZTA');

        // Create the transaction
        $transaction = TransactionBuilder::payment()
            ->sender($msa->toAddress())
            ->receiver($receiver)
            ->flatFee(217000)
            ->amount(5000)
            ->firstValid(972508)
            ->lastValid(973508)
            ->noteB64('tFF5Ofz60nE=')
            ->genesisId('testnet-v31.0')
            ->build();

        $this->assertEquals($transaction->getTransactionId(), $goldenTxId);

        $account = Account::mnemonic('auction inquiry lava second expand liberty glass involve ginger illness length room item discover ahead table doctor term tackle cement bonus profit right above catch');
        $signedTx = $msa->sign($account, $transaction);

        $this->assertEquals($signedTx->toBase64(), $goldenTx);
    }

    public function testSignBytes()
    {
        $values = random_bytes(15);
        $account = Account::random();
        $signature = $account->signBytes($values);
        $verified = $account->getAddress()->verify($values, $signature);
        $this->assertTrue($verified);

        $firstByte = ord($values[0]);
        $firstByte = ($firstByte + 1) % 256;
        $values[0] = chr($firstByte);

        $verified = $account->getAddress()->verify($values, $signature);
        $this->assertFalse($verified);
    }

    public function testVerifyBytes()
    {
        $message = Base64::decode('rTs7+dUj');
        $signature = new Signature(Base64::decode('COEBmoD+ysVECoyVOAsvMAjFxvKeQVkYld+RSHMnEiHsypqrfj2EdYqhrm4t7dK3ZOeSQh3aXiZK/zqQDTPBBw=='));
        $address = Address::fromAlgorandAddress('DPLD3RTSWC5STVBPZL5DIIVE2OC4BSAWTOYBLFN2X6EFLT2ZNF4SMX64UA');

        $verified = $address->verify($message, $signature);
        $this->assertTrue($verified);

        $firstByte = ord($message[0]);
        $firstByte = ($firstByte + 1) % 256;
        $message[0] = chr($firstByte);

        $verified = $address->verify($message, $signature);
        $this->assertFalse($verified);
    }

    public function testLogicSigTransaction()
    {
        $goldenTx = 'gqRsc2lng6NhcmeSxAMxMjPEAzQ1NqFsxAUBIAEBIqNzaWfEQE6HXaI5K0lcq50o/y3bWOYsyw9TLi/oorZB4xaNdn1Z14351u2f6JTON478fl+JhIP4HNRRAIh/I8EWXBPpJQ2jdHhuiqNhbXTNB9CjZmVlzQPoomZ2zgAfeyGjZ2Vuq2Rldm5ldC12MS4womdoxCCwLc/t7ZJ1uookrS1uIJ0r211Klt7pd4IYp2g3OaWPQaJsds4AH38JpG5vdGXECPMTAk7i0PNdo3JjdsQge2ziT+tbrMCxZOKcIixX9fY9w4fUOQSCWEEcX+EPfAKjc25kxCDn8PhNBoEd+fMcjYeLEVX0Zx1RoYXCAJCGZ/RJWHBooaR0eXBlo3BheQ==';

        $sender = Address::fromAlgorandAddress('47YPQTIGQEO7T4Y4RWDYWEKV6RTR2UNBQXBABEEGM72ESWDQNCQ52OPASU');
        $receiver = Address::fromAlgorandAddress('PNWOET7LLOWMBMLE4KOCELCX6X3D3Q4H2Q4QJASYIEOF7YIPPQBG3YQ5YI');
        $account = Account::mnemonic('advice pudding treat near rule blouse same whisper inner electric quit surface sunny dismiss leader blood seat clown cost exist hospital century reform able sponsor');

        // Create the transaction
        $transaction = TransactionBuilder::payment()
            ->sender($sender)
            ->receiver($receiver)
            ->flatFee(1000)
            ->firstValid(2063137)
            ->lastValid(2064137)
            ->noteB64('8xMCTuLQ810=')
            ->genesisId('devnet-v1.0')
            ->genesisHashB64('sC3P7e2SdbqKJK0tbiCdK9tdSpbe6XeCGKdoNzmlj0E=')
            ->amount(2000)
            ->build();

        // Create the logic sig
        $program = Buffer::toBinaryString([0x01, 0x20, 0x01, 0x01, 0x22]);
        $arguments = [Buffer::toBinaryString([49, 50, 51]), Buffer::toBinaryString([52, 53, 54])];

        $lsig = new LogicSignature($program, $arguments);
        $lsig = $lsig->sign($account);
        $signedTx = $lsig->signTransaction($transaction);

        $this->assertEquals($signedTx->toBase64(), $goldenTx);
    }

    public function testTEALSign()
    {
        $data = Base64::decode('Ux8jntyBJQarjKGF8A==');
        $seed = Base64::decode('5Pf7eGMA52qfMT4R4/vYCt7con/7U3yejkdXkrcb26Q=');
        $program = new TEALProgram(Base64::decode('ASABASI='));
        $address = Address::fromAlgorandAddress('6Z3C3LDVWGMX23BMSYMANACQOSINPFIRF77H7N3AWJZYV6OH6GWTJKVMXY');
        $account = Account::seed($seed);

        $signature1 = $address->sign($account, $data);
        $signature2 = $program->sign($account, $data);
        $this->assertEquals($signature1, $signature2);

        // Verify data
        $progDataBytes = utf8_encode('ProgData');
        $buffer = $progDataBytes.$address->address.$data;
        $verified = CryptoUtils::verify($buffer, $signature1->bytes(), $account->getAddress()->address);

        $this->assertTrue($verified);
    }

    public function testToSeed()
    {
        $words = 'actress tongue harbor tray suspect odor load topple vocal avoid ignore apple lunch unknown tissue museum once switch captain place lemon sail outdoor absent creek';
        $mnemonic = Mnemonic::Words($words, WordList::English());
        $seed = hex2bin($mnemonic->entropy);
        $account = Account::seed($seed);
        $this->assertEquals($account->getSeedPhrase()->words, explode(' ', $words));
    }

    private function createTestMsigAddress(): MultiSignatureAddress
    {
        $one = Address::fromAlgorandAddress('DN7MBMCL5JQ3PFUQS7TMX5AH4EEKOBJVDUF4TCV6WERATKFLQF4MQUPZTA');
        $two = Address::fromAlgorandAddress('BFRTECKTOOE7A5LHCF3TTEOH2A7BW46IYT2SX5VP6ANKEXHZYJY77SJTVM');
        $three = Address::fromAlgorandAddress('47YPQTIGQEO7T4Y4RWDYWEKV6RTR2UNBQXBABEEGM72ESWDQNCQ52OPASU');

        $publicKeys = array_map(fn (Address $value) => new Ed25519PublicKey($value->address), [$one, $two, $three]);

        return new MultiSignatureAddress(1, 2, $publicKeys);
    }
}
