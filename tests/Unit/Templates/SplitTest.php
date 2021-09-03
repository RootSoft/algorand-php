<?php


namespace Rootsoft\Algorand\Tests\Unit\Templates;

use Orchestra\Testbench\TestCase;
use ParagonIE\ConstantTime\Base64;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Templates\Split;
use Rootsoft\Algorand\Utils\Buffer;

class SplitTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testSplitContract()
    {
        $goldenAddress = 'HDY7A4VHBWQWQZJBEMASFOUZKBNGWBMJEMUXAGZ4SPIRQ6C24MJHUZKFGY';
        $goldenProgram = 'ASAIAcCWsQICAMDEB2QekE4mAyCztwQn0+DycN+vsk+vJWcsoz/b7NDS6i33HOkvTpf+YiC3qUpIgHGWE8/1LPh9SGCalSN7IaITeeWSXbfsS5wsXyC4kBQ38Z8zcwWVAym4S8vpFB/c0XC6R4mnPi9EBADsPDEQIhIxASMMEDIEJBJAABkxCSgSMQcyAxIQMQglEhAxAiEEDRAiQAAuMwAAMwEAEjEJMgMSEDMABykSEDMBByoSEDMACCEFCzMBCCEGCxIQMwAIIQcPEBA=';
        $goldenTx = 'gqRsc2lngaFsxM4BIAgBwJaxAgIAwMQHZB6QTiYDILO3BCfT4PJw36+yT68lZyyjP9vs0NLqLfcc6S9Ol/5iILepSkiAcZYTz/Us+H1IYJqVI3shohN55ZJdt+xLnCxfILiQFDfxnzNzBZUDKbhLy+kUH9zRcLpHiac+L0QEAOw8MRAiEjEBIwwQMgQkEkAAGTEJKBIxBzIDEhAxCCUSEDECIQQNECJAAC4zAAAzAQASMQkyAxIQMwAHKRIQMwEHKhIQMwAIIQULMwEIIQYLEhAzAAghBw8QEKN0eG6Jo2FtdM4ABJPgo2ZlZc4AId/gomZ2AaJnaMQgf4OxZX/x/FO5LcGBSKHWXfwtSx+j1ncoSt3SABJtkGmjZ3JwxCBLA74bTV35FJNL1h0K9ZbRU24b4M1JRkD1YTogvvDXbqJsdmSjcmN2xCC3qUpIgHGWE8/1LPh9SGCalSN7IaITeeWSXbfsS5wsX6NzbmTEIDjx8HKnDaFoZSEjASK6mVBaawWJIylwGzyT0Rh4WuMSpHR5cGWjcGF5gqRsc2lngaFsxM4BIAgBwJaxAgIAwMQHZB6QTiYDILO3BCfT4PJw36+yT68lZyyjP9vs0NLqLfcc6S9Ol/5iILepSkiAcZYTz/Us+H1IYJqVI3shohN55ZJdt+xLnCxfILiQFDfxnzNzBZUDKbhLy+kUH9zRcLpHiac+L0QEAOw8MRAiEjEBIwwQMgQkEkAAGTEJKBIxBzIDEhAxCCUSEDECIQQNECJAAC4zAAAzAQASMQkyAxIQMwAHKRIQMwEHKhIQMwAIIQULMwEIIQYLEhAzAAghBw8QEKN0eG6Jo2FtdM4AD0JAo2ZlZc4AId/gomZ2AaJnaMQgf4OxZX/x/FO5LcGBSKHWXfwtSx+j1ncoSt3SABJtkGmjZ3JwxCBLA74bTV35FJNL1h0K9ZbRU24b4M1JRkD1YTogvvDXbqJsdmSjcmN2xCC4kBQ38Z8zcwWVAym4S8vpFB/c0XC6R4mnPi9EBADsPKNzbmTEIDjx8HKnDaFoZSEjASK6mVBaawWJIylwGzyT0Rh4WuMSpHR5cGWjcGF5';

        $owner = Address::fromAlgorandAddress('WO3QIJ6T4DZHBX5PWJH26JLHFSRT7W7M2DJOULPXDTUS6TUX7ZRIO4KDFY');
        $receiver1 = Address::fromAlgorandAddress('W6UUUSEAOGLBHT7VFT4H2SDATKKSG6ZBUIJXTZMSLW36YS44FRP5NVAU7U');
        $receiver2 = Address::fromAlgorandAddress('XCIBIN7RT4ZXGBMVAMU3QS6L5EKB7XGROC5EPCNHHYXUIBAA5Q6C5Y7NEU');
        $minPay = 10000;
        $rat1 = 30;
        $rat2 = 100;
        $expiryRound = 123456;
        $maxFee = 5000000;

        // Create the contract
        $contract = Split::create($owner, $receiver1, $receiver2, $rat1, $rat2, $expiryRound, $minPay, $maxFee);

        $this->assertEquals($contract->getAddress()->encodedAddress, $goldenAddress);
        $this->assertEquals($contract->getProgram(), Base64::decode($goldenProgram));

        // Get the transactions
        $gh = 'f4OxZX/x/FO5LcGBSKHWXfwtSx+j1ncoSt3SABJtkGk=';
        $transactions = Split::getTransactions($contract, $minPay * ($rat1 + $rat2), 1, 100, $gh, 10000);

        $this->assertEquals(Base64::encode($transactions), $goldenTx);
    }
}
