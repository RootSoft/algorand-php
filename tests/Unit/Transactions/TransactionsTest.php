<?php

namespace Rootsoft\Algorand\Tests\Unit\Transactions;

use Brick\Math\BigInteger;
use Orchestra\Testbench\TestCase;
use ParagonIE\ConstantTime\Base64;
use Rootsoft\Algorand\Crypto\LogicSignature;
use Rootsoft\Algorand\Exceptions\AlgorandException;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Applications\OnCompletion;
use Rootsoft\Algorand\Models\Applications\StateSchema;
use Rootsoft\Algorand\Models\Applications\TEALProgram;
use Rootsoft\Algorand\Models\Keys\ParticipationPublicKey;
use Rootsoft\Algorand\Models\Keys\VRFPublicKey;
use Rootsoft\Algorand\Models\Transactions\AtomicTransfer;
use Rootsoft\Algorand\Models\Transactions\Builders\TransactionBuilder;
use Rootsoft\Algorand\Models\Transactions\SignedTransaction;
use Rootsoft\Algorand\Models\Transactions\Types\ApplicationBaseTransaction;
use Rootsoft\Algorand\Models\Transactions\Types\ApplicationCreateTransaction;
use Rootsoft\Algorand\Models\Transactions\Types\ApplicationUpdateTransaction;
use Rootsoft\Algorand\Models\Transactions\Types\AssetConfigTransaction;
use Rootsoft\Algorand\Models\Transactions\Types\AssetFreezeTransaction;
use Rootsoft\Algorand\Models\Transactions\Types\AssetTransferTransaction;
use Rootsoft\Algorand\Models\Transactions\Types\KeyRegistrationTransaction;
use Rootsoft\Algorand\Models\Transactions\Types\RawPaymentTransaction;
use Rootsoft\Algorand\Utils\AlgorandUtils;
use Rootsoft\Algorand\Utils\Buffer;
use Rootsoft\Algorand\Utils\Encoder;

class TransactionsTest extends TestCase
{
    public Account $account;

    protected function setUp(): void
    {
        parent::setUp();
        $this->account = Account::mnemonic('awful drop leaf tennis indoor begin mandate discover uncle seven only coil atom any hospital uncover make any climb actor armed measure need above hundred');
    }

    public function testSerializationMessagePack()
    {
        $sender = Address::fromAlgorandAddress('VKM6KSCTDHEM6KGEAMSYCNEGIPFJMHDSEMIRAQLK76CJDIRMMDHKAIRMFQ');
        $receiver = Address::fromAlgorandAddress('VKM6KSCTDHEM6KGEAMSYCNEGIPFJMHDSEMIRAQLK76CJDIRMMDHKAIRMFQ');

        $transaction = TransactionBuilder::payment()
            ->sender($sender)
            ->receiver($receiver)
            ->amount(100)
            ->firstValid(301)
            ->lastValid(1300)
            ->build();

        $encodedTx = Encoder::getInstance()->encodeMessagePack($transaction->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, RawPaymentTransaction::class);
        $this->assertEquals($transaction, $decodedTx);
    }

    public function testPaymentTransaction()
    {
        $sender = Address::fromAlgorandAddress('VKM6KSCTDHEM6KGEAMSYCNEGIPFJMHDSEMIRAQLK76CJDIRMMDHKAIRMFQ');
        $receiver = Address::fromAlgorandAddress('VKM6KSCTDHEM6KGEAMSYCNEGIPFJMHDSEMIRAQLK76CJDIRMMDHKAIRMFQ');

        $transaction = TransactionBuilder::payment()
            ->sender($sender)
            ->receiver($receiver)
            ->amount(100)
            ->firstValid(301)
            ->lastValid(1300)
            ->build();

        // Assert unsigned tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($transaction->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, RawPaymentTransaction::class);

        $this->assertEquals($transaction, $decodedTx);

        $signedTx = $transaction->sign($this->account);

        // Assert signed tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($signedTx->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, SignedTransaction::class);
        $this->assertEquals($decodedTx, $signedTx);
    }

    public function testAssetConfigTransaction()
    {
        $goldenTx = 'gqNzaWfEQF2hf4SoXzT2Wyp5p3CYbMoX2xmrRrKfxxqSa8uhSXv+qDpAIdvFVlQhkNXpz8j7m7M/9xjPBSXSUxnYuzbgvQijdHhuh6RhcGFyiaJhbcQgZkFDUE80blJnTzU1ajFuZEFLM1c2U2djNEFQa2N5RmiiYW6odGVzdGNvaW6iYXXZYHd3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d6FjxCAJ+9J2LAj4bFrmv23Xp6kB3mZ111Dgfoxcdphkfbbh/aFmxCAJ+9J2LAj4bFrmv23Xp6kB3mZ111Dgfoxcdphkfbbh/aFtxCAJ+9J2LAj4bFrmv23Xp6kB3mZ111Dgfoxcdphkfbbh/aFyxCAJ+9J2LAj4bFrmv23Xp6kB3mZ111Dgfoxcdphkfbbh/aF0ZKJ1bqN0c3SjZmVlzRM4omZ2zgAE7A+iZ2jEIEhjtRiks8hOyBDyLU8QgcsPcfBZp6wg3sYvf3DlCToiomx2zgAE7/ejc25kxCAJ+9J2LAj4bFrmv23Xp6kB3mZ111Dgfoxcdphkfbbh/aR0eXBlpGFjZmc=';

        $address = Address::fromAlgorandAddress('BH55E5RMBD4GYWXGX5W5PJ5JAHPGM5OXKDQH5DC4O2MGI7NW4H6VOE4CP4');
        $url = array_fill(0, 96, 'w');

        $transaction = TransactionBuilder::assetConfig()
            ->sender($address)
            ->suggestedFeePerByte(10)
            ->firstValid(322575)
            ->lastValid(323575)
            ->genesisHashB64('SGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9/cOUJOiI=')
            ->totalAssetsToCreate(BigInteger::of(100))
            ->decimals(0)
            ->unitName('tst')
            ->assetName('testcoin')
            ->url(implode($url))
            ->metadataText('fACPO4nRgO55j1ndAK3W6Sgc4APkcyFh')
            ->managerAddress($address)
            ->reserveAddress($address)
            ->freezeAddress($address)
            ->clawbackAddress($address)
            ->build();

        // Assert unsigned tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($transaction->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, AssetConfigTransaction::class);

        $this->assertEquals($transaction, $decodedTx);

        $signedTx = $transaction->sign($this->account);

        // Assert signed tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($signedTx->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, SignedTransaction::class);
        $this->assertEquals($decodedTx, $signedTx);

        // Assert golden
        $golden = Encoder::getInstance()->decodeMessagePack(Base64::decode($goldenTx), SignedTransaction::class);
        $this->assertEquals($decodedTx->toMessagePack(), $golden->toMessagePack());
        $this->assertEquals(Base64::encode(Encoder::getInstance()->encodeMessagePack($decodedTx->toMessagePack())), $goldenTx);
    }

    public function testAssetConfigTransactionWithDecimals()
    {
        $goldenTx = 'gqNzaWfEQGMrl8xmewPhzZL2aLc7Wt+ZI8Ff1HXxA+xO11kbChe/tPIC5scCHv6M+cgTLl1nG9Z0406ScpoeNoIDpcLPXgujdHhuh6RhcGFyiqJhbcQgZkFDUE80blJnTzU1ajFuZEFLM1c2U2djNEFQa2N5RmiiYW6odGVzdGNvaW6iYXXZYHd3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d3d6FjxCAJ+9J2LAj4bFrmv23Xp6kB3mZ111Dgfoxcdphkfbbh/aJkYwGhZsQgCfvSdiwI+Gxa5r9t16epAd5mdddQ4H6MXHaYZH224f2hbcQgCfvSdiwI+Gxa5r9t16epAd5mdddQ4H6MXHaYZH224f2hcsQgCfvSdiwI+Gxa5r9t16epAd5mdddQ4H6MXHaYZH224f2hdGSidW6jdHN0o2ZlZc0TYKJmds4ABOwPomdoxCBIY7UYpLPITsgQ8i1PEIHLD3HwWaesIN7GL39w5Qk6IqJsds4ABO/3o3NuZMQgCfvSdiwI+Gxa5r9t16epAd5mdddQ4H6MXHaYZH224f2kdHlwZaRhY2Zn';

        $address = Address::fromAlgorandAddress('BH55E5RMBD4GYWXGX5W5PJ5JAHPGM5OXKDQH5DC4O2MGI7NW4H6VOE4CP4');
        $url = array_fill(0, 96, 'w');

        $transaction = TransactionBuilder::assetConfig()
            ->sender($address)
            ->suggestedFeePerByte(10)
            ->firstValid(322575)
            ->lastValid(323575)
            ->genesisHashB64('SGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9/cOUJOiI=')
            ->totalAssetsToCreate(BigInteger::of(100))
            ->decimals(1)
            ->unitName('tst')
            ->assetName('testcoin')
            ->url(implode($url))
            ->metadataText('fACPO4nRgO55j1ndAK3W6Sgc4APkcyFh')
            ->managerAddress($address)
            ->reserveAddress($address)
            ->freezeAddress($address)
            ->clawbackAddress($address)
            ->build();

        // Assert unsigned tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($transaction->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, AssetConfigTransaction::class);

        $this->assertEquals($transaction, $decodedTx);

        $signedTx = $transaction->sign($this->account);

        // Assert signed tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($signedTx->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, SignedTransaction::class);
        $this->assertEquals($decodedTx, $signedTx);

        // Assert golden
        $golden = Encoder::getInstance()->decodeMessagePack(Base64::decode($goldenTx), SignedTransaction::class);
        $this->assertEquals($decodedTx->toMessagePack(), $golden->toMessagePack());
        $this->assertEquals(Base64::encode(Encoder::getInstance()->encodeMessagePack($decodedTx->toMessagePack())), $goldenTx);
    }

    public function testAssetFreeze()
    {
        $goldenTx = 'gqNzaWfEQAhru5V2Xvr19s4pGnI0aslqwY4lA2skzpYtDTAN9DKSH5+qsfQQhm4oq+9VHVj7e1rQC49S28vQZmzDTVnYDQGjdHhuiaRhZnJ6w6RmYWRkxCAJ+9J2LAj4bFrmv23Xp6kB3mZ111Dgfoxcdphkfbbh/aRmYWlkAaNmZWXNCRqiZnbOAATsD6JnaMQgSGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9/cOUJOiKibHbOAATv+KNzbmTEIAn70nYsCPhsWua/bdenqQHeZnXXUOB+jFx2mGR9tuH9pHR5cGWkYWZyeg==';

        $address = Address::fromAlgorandAddress('BH55E5RMBD4GYWXGX5W5PJ5JAHPGM5OXKDQH5DC4O2MGI7NW4H6VOE4CP4');

        $transaction = TransactionBuilder::assetFreeze()
            ->sender($address)
            ->freezeTarget($address)
            ->freeze(true)
            ->flatFee(10)
            ->firstValid(322575)
            ->lastValid(323576)
            ->assetId(BigInteger::of(1))
            ->genesisHashB64('SGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9/cOUJOiI=')
            ->build();

        $transaction->setFeeByFeePerByte(BigInteger::of(10));

        // Assert unsigned tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($transaction->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, AssetFreezeTransaction::class);

        $this->assertEquals($transaction, $decodedTx);

        $signedTx = $transaction->sign($this->account);

        // Assert signed tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($signedTx->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, SignedTransaction::class);
        $this->assertEquals($decodedTx, $signedTx);

        // Assert golden
        $golden = Encoder::getInstance()->decodeMessagePack(Base64::decode($goldenTx), SignedTransaction::class);
        $this->assertEquals($decodedTx->toMessagePack(), $golden->toMessagePack());
        $this->assertEquals(Base64::encode(Encoder::getInstance()->encodeMessagePack($decodedTx->toMessagePack())), $goldenTx);
    }

    public function testAssetTransfer()
    {
        $goldenTx = 'gqNzaWfEQHsgfEAmEHUxLLLR9s+Y/yq5WeoGo/jAArCbany+7ZYwExMySzAhmV7M7S8+LBtJalB4EhzEUMKmt3kNKk6+vAWjdHhuiqRhYW10AaRhcmN2xCAJ+9J2LAj4bFrmv23Xp6kB3mZ111Dgfoxcdphkfbbh/aRhc25kxCAJ+9J2LAj4bFrmv23Xp6kB3mZ111Dgfoxcdphkfbbh/aNmZWXNCqqiZnbOAATsD6JnaMQgSGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9/cOUJOiKibHbOAATv96NzbmTEIAn70nYsCPhsWua/bdenqQHeZnXXUOB+jFx2mGR9tuH9pHR5cGWlYXhmZXKkeGFpZAE=';

        $address = Address::fromAlgorandAddress('BH55E5RMBD4GYWXGX5W5PJ5JAHPGM5OXKDQH5DC4O2MGI7NW4H6VOE4CP4');

        $transaction = TransactionBuilder::assetTransfer()
            ->sender($address)
            ->assetSender($address)
            ->assetReceiver($address)
            ->amount(1)
            ->flatFee(10)
            ->firstValid(322575)
            ->lastValid(323575)
            ->assetId(BigInteger::of(1))
            ->genesisHashB64('SGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9/cOUJOiI=')
            ->build();

        $transaction->setFeeByFeePerByte(BigInteger::of(10));

        // Assert unsigned tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($transaction->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, AssetTransferTransaction::class);

        $this->assertEquals($transaction, $decodedTx);

        $signedTx = $transaction->sign($this->account);

        // Assert signed tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($signedTx->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, SignedTransaction::class);
        $this->assertEquals($decodedTx, $signedTx);

        // Assert golden
        $golden = Encoder::getInstance()->decodeMessagePack(Base64::decode($goldenTx), SignedTransaction::class);
        $this->assertEquals($decodedTx->toMessagePack(), $golden->toMessagePack());
        $this->assertEquals(Base64::encode(Encoder::getInstance()->encodeMessagePack($decodedTx->toMessagePack())), $goldenTx);
    }

    public function testKeyRegistration()
    {
        $address = Address::fromAlgorandAddress('BH55E5RMBD4GYWXGX5W5PJ5JAHPGM5OXKDQH5DC4O2MGI7NW4H6VOE4CP4');

        $transaction = TransactionBuilder::keyRegistration()
            ->sender($address)
            ->votePublicKey(new ParticipationPublicKey(random_bytes(32)))
            ->selectionPublicKey(new VRFPublicKey(random_bytes(32)))
            ->voteFirst(322575)
            ->voteLast(323576)
            ->voteKeyDilution(10000)
            ->flatFee(10)
            ->firstValid(322575)
            ->lastValid(323576)
            ->genesisHashB64('SGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9/cOUJOiI=')
            ->build();

        $transaction->setFeeByFeePerByte(BigInteger::of(10));

        // Assert unsigned tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($transaction->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, KeyRegistrationTransaction::class);

        $this->assertEquals($transaction, $decodedTx);

        $signedTx = $transaction->sign($this->account);

        // Assert signed tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($signedTx->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, SignedTransaction::class);
        $this->assertEquals($decodedTx, $signedTx);
    }

    public function testApplicationCall()
    {
        $goldenTx = 'gqNzaWfEQLNZTKVB+TyomQ2NgR4o8HXyvurnGQLKIO0xiXLQnqTT+n4Ck7Te/0dDbqafy0ZmLVCYgReVOtcGzdHYr5TcdQCjdHhui6RhcGFhksQEYXJnMcQBDKRhcGFzkc4ABj1zpGFwYXSRxCAJ+9J2LAj4bFrmv23Xp6kB3mZ111Dgfoxcdphkfbbh/aRhcGZhkc4BU156pGFwaWTOAYT8baNmZWXNCoKiZnbOAATsD6JnaMQgSGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9/cOUJOiKibHbOAATv96NzbmTEIAn70nYsCPhsWua/bdenqQHeZnXXUOB+jFx2mGR9tuH9pHR5cGWkYXBwbA==';

        $address = Address::fromAlgorandAddress('BH55E5RMBD4GYWXGX5W5PJ5JAHPGM5OXKDQH5DC4O2MGI7NW4H6VOE4CP4');
        $arguments = AlgorandUtils::parse_application_arguments('str:arg1,int:12');

        $transaction = TransactionBuilder::applicationCall()
            ->sender($address)
            ->applicationId(BigInteger::of(25492589))
            ->onCompletion(OnCompletion::NO_OP_OC())
            ->arguments($arguments)
            ->accounts([$address])
            ->foreignApps([22240890])
            ->foreignAssets([408947])
            ->flatFee(10)
            ->firstValid(322575)
            ->lastValid(323575)
            ->genesisHashB64('SGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9/cOUJOiI=')
            ->build();

        $transaction->setFeeByFeePerByte(BigInteger::of(10));

        // Assert unsigned tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($transaction->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, ApplicationBaseTransaction::class);

        $this->assertEquals($transaction, $decodedTx);

        $signedTx = $transaction->sign($this->account);

        // Assert signed tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($signedTx->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, SignedTransaction::class);
        $this->assertEquals($decodedTx, $signedTx);

        // Assert golden
        $golden = Encoder::getInstance()->decodeMessagePack(Base64::decode($goldenTx), SignedTransaction::class);
        $this->assertEquals($decodedTx->toMessagePack(), $golden->toMessagePack());
        $this->assertEquals(Base64::encode(Encoder::getInstance()->encodeMessagePack($decodedTx->toMessagePack())), $goldenTx);
    }

    public function testApplicationUpdate()
    {
        $goldenTx = 'gqNzaWfEQOWwYXntsrpTkMmpAqWpAowM7dgfpMA/WWCzvx+3n3Yvc2C/BJoGG/JXHrrIk0tWGBjPm4Eth+vv3NeMC3ufhAOjdHhuiaRhcGFuBKRhcGFwxBYEgAdjb3VudGVySWSBAQhJNQBnNABDpGFwc3XEAwSBAaNmZWXNCN6iZnbOAATsD6JnaMQgSGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9/cOUJOiKibHbOAATv96NzbmTEIAn70nYsCPhsWua/bdenqQHeZnXXUOB+jFx2mGR9tuH9pHR5cGWkYXBwbA==';

        $address = Address::fromAlgorandAddress('BH55E5RMBD4GYWXGX5W5PJ5JAHPGM5OXKDQH5DC4O2MGI7NW4H6VOE4CP4');
        $approvalProgram = Base64::decode('BIAHY291bnRlcklkgQEISTUAZzQAQw==');
        $clearProgram = Base64::decode('BIEB');

        // Create the application transaction
        $transaction = TransactionBuilder::applicationUpdate()
            ->sender($address)
            ->approvalProgram(new TEALProgram($approvalProgram))
            ->clearStateProgram(new TEALProgram($clearProgram))
            ->flatFee(10)
            ->firstValid(322575)
            ->lastValid(323575)
            ->genesisHashB64('SGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9/cOUJOiI=')
            ->build();

        $transaction->setFeeByFeePerByte(BigInteger::of(10));

        // Assert unsigned tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($transaction->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, ApplicationUpdateTransaction::class);

        $this->assertEquals($transaction, $decodedTx);

        $signedTx = $transaction->sign($this->account);

        // Assert signed tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($signedTx->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, SignedTransaction::class);
        $this->assertEquals($decodedTx, $signedTx);

        // Assert golden
        $golden = Encoder::getInstance()->decodeMessagePack(Base64::decode($goldenTx), SignedTransaction::class);
        $this->assertEquals($decodedTx->toMessagePack(), $golden->toMessagePack());
        $this->assertEquals(Base64::encode(Encoder::getInstance()->encodeMessagePack($decodedTx->toMessagePack())), $goldenTx);
    }

    public function testApplicationCreate()
    {
        $goldenTx = 'gqNzaWfEQINUBZn3j25Jffpf1FWw4NYtGd9Njlz7l03lOu4Nt+v9nXOlqVGbDmFrnFCWKo8ecAtEUd4ltzNZZ4ei5FOd9Q+jdHhuiqRhcGFwxBYEgAdjb3VudGVySWSBAQhJNQBnNABDpGFwZ3OBo251aQGkYXBsc4KjbmJzAaNudWkBpGFwc3XEAwSBAaNmZWXNCbCiZnbOAATsD6JnaMQgSGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9/cOUJOiKibHbOAATv96NzbmTEIAn70nYsCPhsWua/bdenqQHeZnXXUOB+jFx2mGR9tuH9pHR5cGWkYXBwbA==';

        $address = Address::fromAlgorandAddress('BH55E5RMBD4GYWXGX5W5PJ5JAHPGM5OXKDQH5DC4O2MGI7NW4H6VOE4CP4');
        $approvalProgram = Base64::decode('BIAHY291bnRlcklkgQEISTUAZzQAQw==');
        $clearProgram = Base64::decode('BIEB');

        // Declare schema
        $localInts = 1;
        $localBytes = 1;
        $globalInts = 1;
        $globalBytes = 0;

        // Create the application transaction
        $transaction = TransactionBuilder::applicationCreate()
            ->sender($address)
            ->approvalProgram(new TEALProgram($approvalProgram))
            ->clearStateProgram(new TEALProgram($clearProgram))
            ->globalStateSchema(new StateSchema($globalInts, $globalBytes))
            ->localStateSchema(new StateSchema($localInts, $localBytes))
            ->flatFee(10)
            ->firstValid(322575)
            ->lastValid(323575)
            ->genesisHashB64('SGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9/cOUJOiI=')
            ->build();

        $transaction->setFeeByFeePerByte(BigInteger::of(10));

        // Assert unsigned tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($transaction->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, ApplicationCreateTransaction::class);
        $this->assertEquals($transaction->toMessagePack(), $decodedTx->toMessagePack());

        $signedTx = $transaction->sign($this->account);

        // Assert signed tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($signedTx->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, SignedTransaction::class);
        $this->assertEquals($decodedTx, $signedTx);

        // Assert golden
        $golden = Encoder::getInstance()->decodeMessagePack(Base64::decode($goldenTx), SignedTransaction::class);
        $this->assertEquals($decodedTx->toMessagePack(), $golden->toMessagePack());
        $this->assertEquals(Base64::encode(Encoder::getInstance()->encodeMessagePack($decodedTx->toMessagePack())), $goldenTx);
    }

    public function testLogicSignature()
    {
        $goldenTx = 'gqRsc2lngaFsxAUBIAEBIqN0eG6Io2FtdGSjZmVlzQiYomZ2zQEtomdoxCBIY7UYpLPITsgQ8i1PEIHLD3HwWaesIN7GL39w5Qk6IqJsds0FFKNyY3bEIAn70nYsCPhsWua/bdenqQHeZnXXUOB+jFx2mGR9tuH9o3NuZMQg9nYtrHWxmX1sLJYYBoBQdJDXlREv/n+3YLJzivnH8a2kdHlwZaNwYXk=';
        $program = Buffer::toBinaryString([0x01, 0x20, 0x01, 0x01, 0x22]);
        $lsig = new LogicSignature($program);

        $address = Address::fromAlgorandAddress('BH55E5RMBD4GYWXGX5W5PJ5JAHPGM5OXKDQH5DC4O2MGI7NW4H6VOE4CP4');

        $transaction = TransactionBuilder::payment()
            ->sender($lsig->toAddress())
            ->receiver($address)
            ->amount(100)
            ->firstValid(301)
            ->lastValid(1300)
            ->genesisHashB64('SGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9/cOUJOiI=')
            ->build();

        $transaction->setFeeByFeePerByte(BigInteger::of(10));

        // Assert unsigned tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($transaction->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, RawPaymentTransaction::class);

        $this->assertEquals($transaction, $decodedTx);

        // Sign the logic transaction
        $signedTx = $lsig->signTransaction($transaction);

        // Assert signed tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($signedTx->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, SignedTransaction::class);
        $this->assertEquals($decodedTx, $signedTx);

        // Assert golden
        $this->assertEquals(Base64::encode(Encoder::getInstance()->encodeMessagePack($decodedTx->toMessagePack())), $goldenTx);
    }

    public function testLogicSignatureWithArguments()
    {
        $goldenTx = 'gqRsc2lngqNhcmeRxAF7oWzEBQEgAQEio3R4boijYW10ZKNmZWXNCJiiZnbNAS2iZ2jEIEhjtRiks8hOyBDyLU8QgcsPcfBZp6wg3sYvf3DlCToiomx2zQUUo3JjdsQgCfvSdiwI+Gxa5r9t16epAd5mdddQ4H6MXHaYZH224f2jc25kxCD2di2sdbGZfWwslhgGgFB0kNeVES/+f7dgsnOK+cfxraR0eXBlo3BheQ==';
        $program = Buffer::toBinaryString([0x01, 0x20, 0x01, 0x01, 0x22]);
        $arguments = [BigInteger::of(123)->toBytes()];
        $lsig = new LogicSignature($program, $arguments);

        $address = Address::fromAlgorandAddress('BH55E5RMBD4GYWXGX5W5PJ5JAHPGM5OXKDQH5DC4O2MGI7NW4H6VOE4CP4');

        $transaction = TransactionBuilder::payment()
            ->sender($lsig->toAddress())
            ->receiver($address)
            ->amount(100)
            ->firstValid(301)
            ->lastValid(1300)
            ->genesisHashB64('SGO1GKSzyE7IEPItTxCByw9x8FmnrCDexi9/cOUJOiI=')
            ->build();

        $transaction->setFeeByFeePerByte(BigInteger::of(10));

        // Assert unsigned tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($transaction->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, RawPaymentTransaction::class);

        $this->assertEquals($transaction, $decodedTx);

        // Sign the logic transaction
        $signedTx = $lsig->signTransaction($transaction);

        // Assert signed tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($signedTx->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, SignedTransaction::class);
        $this->assertEquals($decodedTx, $signedTx);

        // Assert golden
        $this->assertEquals(Base64::encode(Encoder::getInstance()->encodeMessagePack($decodedTx->toMessagePack())), $goldenTx);
    }

    public function testLease()
    {
        $goldenTx = 'gqNzaWfEQOMmFSIKsZvpW0txwzhmbgQjxv6IyN7BbV5sZ2aNgFbVcrWUnqPpQQxfPhV/wdu9jzEPUU1jAujYtcNCxJ7ONgejdHhujKNhbXTNA+ilY2xvc2XEIEDpNJKIJWTLzpxZpptnVCaJ6aHDoqnqW2Wm6KRCH/xXo2ZlZc0FLKJmds0wsqNnZW6sZGV2bmV0LXYzMy4womdoxCAmCyAJoJOohot5WHIvpeVG7eftF+TYXEx4r7BFJpDt0qJsds00mqJseMQgAQIDBAECAwQBAgMEAQIDBAECAwQBAgMEAQIDBAECAwSkbm90ZcQI6gAVR0Nsv5ajcmN2xCB7bOJP61uswLFk4pwiLFf19j3Dh9Q5BIJYQRxf4Q98AqNzbmTEIOfw+E0GgR358xyNh4sRVfRnHVGhhcIAkIZn9ElYcGihpHR5cGWjcGF5';
        $sender = Address::fromAlgorandAddress('47YPQTIGQEO7T4Y4RWDYWEKV6RTR2UNBQXBABEEGM72ESWDQNCQ52OPASU');
        $receiver = Address::fromAlgorandAddress('PNWOET7LLOWMBMLE4KOCELCX6X3D3Q4H2Q4QJASYIEOF7YIPPQBG3YQ5YI');
        $closeTo = Address::fromAlgorandAddress('IDUTJEUIEVSMXTU4LGTJWZ2UE2E6TIODUKU6UW3FU3UKIQQ77RLUBBBFLA');

        $account = Account::mnemonic('advice pudding treat near rule blouse same whisper inner electric quit surface sunny dismiss leader blood seat clown cost exist hospital century reform able sponsor');

        $transaction = TransactionBuilder::payment()
            ->sender($sender)
            ->suggestedFeePerByte(4)
            ->receiver($receiver)
            ->amount(1000)
            ->firstValid(12466)
            ->lastValid(13466)
            ->noteB64('6gAVR0Nsv5Y=')
            ->genesisId('devnet-v33.0')
            ->genesisHashB64('JgsgCaCTqIaLeVhyL6XlRu3n7Rfk2FxMeK+wRSaQ7dI=')
            ->closeRemainderTo($closeTo)
            ->leaseB64('AQIDBAECAwQBAgMEAQIDBAECAwQBAgMEAQIDBAECAwQ=')
            ->build();

        // Assert unsigned tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($transaction->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, RawPaymentTransaction::class);

        $this->assertEquals($transaction, $decodedTx);

        $signedTx = $transaction->sign($account);

        // Assert signed tx
        $encodedTx = Encoder::getInstance()->encodeMessagePack($signedTx->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, SignedTransaction::class);
        $this->assertEquals($decodedTx, $signedTx);

        // Assert golden
        $golden = Encoder::getInstance()->decodeMessagePack(Base64::decode($goldenTx), SignedTransaction::class);
        $this->assertEquals($decodedTx->toMessagePack(), $golden->toMessagePack());
        $this->assertEquals(Base64::encode(Encoder::getInstance()->encodeMessagePack($decodedTx->toMessagePack())), $goldenTx);
    }

    public function testTransactionGroup()
    {
        $sender = Address::fromAlgorandAddress('UPYAFLHSIPMJOHVXU2MPLQ46GXJKSDCEMZ6RLCQ7GWB5PRDKJUWKKXECXI');
        $receiver = Address::fromAlgorandAddress('UPYAFLHSIPMJOHVXU2MPLQ46GXJKSDCEMZ6RLCQ7GWB5PRDKJUWKKXECXI');

        $tx1 = TransactionBuilder::payment()
            ->sender($sender)
            ->flatFee(1000)
            ->receiver($receiver)
            ->amount(2000)
            ->firstValid(710399)
            ->lastValid(710399 + 1000)
            ->noteB64('wRKw5cJ0CMo=')
            ->genesisId('devnet-v1.0')
            ->genesisHashB64('sC3P7e2SdbqKJK0tbiCdK9tdSpbe6XeCGKdoNzmlj0E=')
            ->build();

        $tx2 = TransactionBuilder::payment()
            ->sender($sender)
            ->flatFee(1000)
            ->receiver($receiver)
            ->amount(2000)
            ->firstValid(710515)
            ->lastValid(710515 + 1000)
            ->noteB64('dBlHI6BdrIg=')
            ->genesisId('devnet-v1.0')
            ->genesisHashB64('sC3P7e2SdbqKJK0tbiCdK9tdSpbe6XeCGKdoNzmlj0E=')
            ->build();

        // Check serialization without group
        $encodedTx = Encoder::getInstance()->encodeMessagePack($tx1->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, RawPaymentTransaction::class);
        $this->assertEquals($tx1, $decodedTx);

        $encodedTx = Encoder::getInstance()->encodeMessagePack($tx2->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, RawPaymentTransaction::class);
        $this->assertEquals($tx2, $decodedTx);

        AtomicTransfer::group([$tx1, $tx2]);

        // Check serialization with group
        $encodedTx = Encoder::getInstance()->encodeMessagePack($tx1->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, RawPaymentTransaction::class);
        $this->assertEquals($tx1, $decodedTx);

        $encodedTx = Encoder::getInstance()->encodeMessagePack($tx2->toMessagePack());
        $decodedTx = Encoder::getInstance()->decodeMessagePack($encodedTx, RawPaymentTransaction::class);
        $this->assertEquals($tx2, $decodedTx);
    }

    public function testTransactionGroupLimit()
    {
        $this->expectException(AlgorandException::class);
        $sender = Address::fromAlgorandAddress('VKM6KSCTDHEM6KGEAMSYCNEGIPFJMHDSEMIRAQLK76CJDIRMMDHKAIRMFQ');
        $receiver = Address::fromAlgorandAddress('VKM6KSCTDHEM6KGEAMSYCNEGIPFJMHDSEMIRAQLK76CJDIRMMDHKAIRMFQ');

        $tx = TransactionBuilder::payment()
            ->sender($sender)
            ->receiver($receiver)
            ->amount(100)
            ->firstValid(301)
            ->lastValid(1300)
            ->build();

        $transactions = array_fill(0, AtomicTransfer::MAX_TRANSACTION_GROUP_SIZE + 1, $tx);
        AtomicTransfer::computeGroupId($transactions);
    }

    public function testTransactionGroupEmpty()
    {
        $this->expectException(AlgorandException::class);
        AtomicTransfer::computeGroupId([]);
    }
}
