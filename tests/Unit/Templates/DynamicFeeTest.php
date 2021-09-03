<?php


namespace Rootsoft\Algorand\Tests\Unit\Templates;

use Brick\Math\BigInteger;
use Orchestra\Testbench\TestCase;
use ParagonIE\ConstantTime\Base64;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Templates\DynamicFee;
use Rootsoft\Algorand\Templates\Split;
use Rootsoft\Algorand\Utils\Buffer;

class DynamicFeeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testDynamicFee()
    {
        $goldenAddress = 'GCI4WWDIWUFATVPOQ372OZYG52EULPUZKI7Y34MXK3ZJKIBZXHD2H5C5TI';
        $goldenProgram = 'ASAFAgGIJ7lgumAmAyD+vKC7FEpaTqe0OKRoGsgObKEFvLYH/FZTJclWlfaiEyDmmpYeby1feshmB5JlUr6YI17TM2PKiJGLuck4qRW2+SB/g7Flf/H8U7ktwYFIodZd/C1LH6PWdyhK3dIAEm2QaTIEIhIzABAjEhAzAAcxABIQMwAIMQESEDEWIxIQMRAjEhAxBygSEDEJKRIQMQgkEhAxAiUSEDEEIQQSEDEGKhIQ';
        $goldenTx = 'iqNhbXTNE4ilY2xvc2XEIOaalh5vLV96yGYHkmVSvpgjXtMzY8qIkYu5yTipFbb5o2ZlZc0D6KJmds0wOaJnaMQgf4OxZX/x/FO5LcGBSKHWXfwtSx+j1ncoSt3SABJtkGmibHbNMDqibHjEIH+DsWV/8fxTuS3BgUih1l38LUsfo9Z3KErd0gASbZBpo3JjdsQg/ryguxRKWk6ntDikaBrIDmyhBby2B/xWUyXJVpX2ohOjc25kxCCFPYdMJymqcGoxdDeyuM8t6Kxixfq0PJCyJP71uhYT76R0eXBlo3BheQ==';
        $goldenLsig = 'gqFsxLEBIAUCAYgnuWC6YCYDIP68oLsUSlpOp7Q4pGgayA5soQW8tgf8VlMlyVaV9qITIOaalh5vLV96yGYHkmVSvpgjXtMzY8qIkYu5yTipFbb5IH+DsWV/8fxTuS3BgUih1l38LUsfo9Z3KErd0gASbZBpMgQiEjMAECMSEDMABzEAEhAzAAgxARIQMRYjEhAxECMSEDEHKBIQMQkpEhAxCCQSEDECJRIQMQQhBBIQMQYqEhCjc2lnxEAhLNdfdDp9Wbi0YwsEQCpP7TVHbHG7y41F4MoESNW/vL1guS+5Wj4f5V9fmM63/VKTSMFidHOSwm5o+pbV5lYH';
        $goldenTxs = 'gqNzaWfEQJBNVry9qdpnco+uQzwFicUWHteYUIxwDkdHqY5Qw2Q8Fc2StrQUgN+2k8q4rC0LKrTMJQnE+mLWhZgMMJvq3QCjdHhuiqNhbXTOAAWq6qNmZWXOAATzvqJmds0wOaJnaMQgf4OxZX/x/FO5LcGBSKHWXfwtSx+j1ncoSt3SABJtkGmjZ3JwxCCCVfqhCinRBXKMIq9eSrJQIXZ+7iXUTig91oGd/mZEAqJsds0wOqJseMQgf4OxZX/x/FO5LcGBSKHWXfwtSx+j1ncoSt3SABJtkGmjcmN2xCCFPYdMJymqcGoxdDeyuM8t6Kxixfq0PJCyJP71uhYT76NzbmTEICuIj6PMWBK0XH0TqQSTWXj6UWxbhN7Y9jUpXyQ1xxxGpHR5cGWjcGF5gqRsc2lngqFsxLEBIAUCAYgnuWC6YCYDIP68oLsUSlpOp7Q4pGgayA5soQW8tgf8VlMlyVaV9qITIOaalh5vLV96yGYHkmVSvpgjXtMzY8qIkYu5yTipFbb5IH+DsWV/8fxTuS3BgUih1l38LUsfo9Z3KErd0gASbZBpMgQiEjMAECMSEDMABzEAEhAzAAgxARIQMRYjEhAxECMSEDEHKBIQMQkpEhAxCCQSEDECJRIQMQQhBBIQMQYqEhCjc2lnxEAhLNdfdDp9Wbi0YwsEQCpP7TVHbHG7y41F4MoESNW/vL1guS+5Wj4f5V9fmM63/VKTSMFidHOSwm5o+pbV5lYHo3R4boujYW10zROIpWNsb3NlxCDmmpYeby1feshmB5JlUr6YI17TM2PKiJGLuck4qRW2+aNmZWXOAAWq6qJmds0wOaJnaMQgf4OxZX/x/FO5LcGBSKHWXfwtSx+j1ncoSt3SABJtkGmjZ3JwxCCCVfqhCinRBXKMIq9eSrJQIXZ+7iXUTig91oGd/mZEAqJsds0wOqJseMQgf4OxZX/x/FO5LcGBSKHWXfwtSx+j1ncoSt3SABJtkGmjcmN2xCD+vKC7FEpaTqe0OKRoGsgObKEFvLYH/FZTJclWlfaiE6NzbmTEIIU9h0wnKapwajF0N7K4zy3orGLF+rQ8kLIk/vW6FhPvpHR5cGWjcGF5';

        $receiver = Address::fromAlgorandAddress('726KBOYUJJNE5J5UHCSGQGWIBZWKCBN4WYD7YVSTEXEVNFPWUIJ7TAEOPM');
        $closeRemainderTo = Address::fromAlgorandAddress('42NJMHTPFVPXVSDGA6JGKUV6TARV5UZTMPFIREMLXHETRKIVW34QFSDFRE');
        $lease = 'f4OxZX/x/FO5LcGBSKHWXfwtSx+j1ncoSt3SABJtkGk=';

        // Create the contract
        $contract = DynamicFee::create($receiver, 5000, 12345, $closeRemainderTo, 12346, $lease);

        $this->assertEquals($contract->getAddress()->encodedAddress, $goldenAddress);
        $this->assertEquals($contract->getProgram(), Base64::decode($goldenProgram));

        // Sign the contract
        $privateKey1 = Base64::decode('cv8E0Ln24FSkwDgGeuXKStOTGcze5u8yldpXxgrBxumFPYdMJymqcGoxdDeyuM8t6Kxixfq0PJCyJP71uhYT7w==');
        $privateKey2 = Base64::decode('2qjz96Vj9M6YOqtNlfJUOKac13EHCXyDty94ozCjuwwriI+jzFgStFx9E6kEk1l4+lFsW4Te2PY1KV8kNcccRg==');
        $sender = Account::seed(substr($privateKey1, 0, 32));
        $account2 = Account::seed(substr($privateKey2, 0, 32));

        $genesisHash = 'f4OxZX/x/FO5LcGBSKHWXfwtSx+j1ncoSt3SABJtkGk=';
        $dynamicFee = DynamicFee::sign($contract, $sender, $genesisHash);

        $this->assertEquals($dynamicFee->getLogicSignature()->toBase64(), $goldenLsig);
        $this->assertEquals($dynamicFee->getTransaction()->toBase64(), $goldenTx);

        // Get the transactions
        $transactions = DynamicFee::getReimbursementTransactions($dynamicFee->getTransaction(), $dynamicFee->getLogicSignature(), $account2, BigInteger::of(1234));

        $this->assertEquals(Base64::encode($transactions), $goldenTxs);
    }
}
