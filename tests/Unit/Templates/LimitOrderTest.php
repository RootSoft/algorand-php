<?php

namespace Rootsoft\Algorand\Tests\Unit\Templates;

use Orchestra\Testbench\TestCase;
use ParagonIE\ConstantTime\Base64;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Templates\LimitOrder;

class LimitOrderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testLimitOrder()
    {
        $goldenAddress = 'LXQWT2XLIVNFS54VTLR63UY5K6AMIEWI7YTVE6LB4RWZDBZKH22ZO3S36I';
        $goldenProgram = 'ASAKAAHAlrECApBOBLlgZB7AxAcmASD+vKC7FEpaTqe0OKRoGsgObKEFvLYH/FZTJclWlfaiEzEWIhIxECMSEDEBJA4QMgQjEkAAVTIEJRIxCCEEDRAxCTIDEhAzARAhBRIQMwERIQYSEDMBFCgSEDMBEzIDEhAzARIhBx01AjUBMQghCB01BDUDNAE0Aw1AACQ0ATQDEjQCNAQPEEAAFgAxCSgSMQIhCQ0QMQcyAxIQMQgiEhAQ';

        $owner = Address::fromAlgorandAddress('726KBOYUJJNE5J5UHCSGQGWIBZWKCBN4WYD7YVSTEXEVNFPWUIJ7TAEOPM');

        // Create the contract
        $contract = LimitOrder::create($owner, 12345, 30, 100, 123456, 10000, 5000000);

        $this->assertEquals($contract->getAddress()->encodedAddress, $goldenAddress);
        $this->assertEquals($contract->getProgram(), Base64::decode($goldenProgram));

        // Sign the contract
        $privateKey1 = Base64::decode('DTKVj7KMON3GSWBwMX9McQHtaDDi8SDEBi0bt4rOxlHNRahLa0zVG+25BDIaHB1dSoIHIsUQ8FFcdnCdKoG+Bg==');
        $sender = Account::seed(substr($privateKey1, 0, 32));

        // Get the swap transactions
        $genesisHash = 'f4OxZX/x/FO5LcGBSKHWXfwtSx+j1ncoSt3SABJtkGk=';
        $transactions = LimitOrder::getSwapAssetsTransaction($contract, $sender, 3000, 10000, 1234, 2234, $genesisHash, 10);

        $goldenTx1 = 'gqRsc2lngaFsxLcBIAoAAcCWsQICkE4EuWBkHsDEByYBIP68oLsUSlpOp7Q4pGgayA5soQW8tgf8VlMlyVaV9qITMRYiEjEQIxIQMQEkDhAyBCMSQABVMgQlEjEIIQQNEDEJMgMSEDMBECEFEhAzAREhBhIQMwEUKBIQMwETMgMSEDMBEiEHHTUCNQExCCEIHTUENQM0ATQDDUAAJDQBNAMSNAI0BA8QQAAWADEJKBIxAiEJDRAxBzIDEhAxCCISEBCjdHhuiaNhbXTNJxCjZmVlzQisomZ2zQTSomdoxCB/g7Flf/H8U7ktwYFIodZd/C1LH6PWdyhK3dIAEm2QaaNncnDEIKz368WOGpdE/Ww0L8wUu5Ly2u2bpG3ZSMKCJvcvGApTomx2zQi6o3JjdsQgzUWoS2tM1RvtuQQyGhwdXUqCByLFEPBRXHZwnSqBvgajc25kxCBd4Wnq60VaWXeVmuPt0x1XgMQSyP4nUnlh5G2Rhyo+taR0eXBlo3BheQ==';
        $goldenTx2 = 'gqNzaWfEQKXv8Z6OUDNmiZ5phpoQJHmfKyBal4gBZLPYsByYnlXCAlXMBeVFG5CLP1k5L6BPyEG2/XIbjbyM0CGG55CxxAKjdHhuiqRhYW10zQu4pGFyY3bEIP68oLsUSlpOp7Q4pGgayA5soQW8tgf8VlMlyVaV9qITo2ZlZc0JJKJmds0E0qJnaMQgf4OxZX/x/FO5LcGBSKHWXfwtSx+j1ncoSt3SABJtkGmjZ3JwxCCs9+vFjhqXRP1sNC/MFLuS8trtm6Rt2UjCgib3LxgKU6Jsds0IuqNzbmTEIM1FqEtrTNUb7bkEMhocHV1KggcixRDwUVx2cJ0qgb4GpHR5cGWlYXhmZXKkeGFpZM0wOQ==';

        $this->assertEquals($transactions, Base64::decode($goldenTx1).Base64::decode($goldenTx2));
    }
}
