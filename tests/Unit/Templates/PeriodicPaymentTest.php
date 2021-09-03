<?php


namespace Rootsoft\Algorand\Tests\Unit\Templates;

use Orchestra\Testbench\TestCase;
use ParagonIE\ConstantTime\Base64;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Templates\PeriodicPayment;
use Rootsoft\Algorand\Templates\Split;
use Rootsoft\Algorand\Utils\Buffer;

class PeriodicPaymentTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testPeriodicPayment()
    {
        $goldenAddress = 'JMS3K4LSHPULANJIVQBTEDP5PZK6HHMDQS4OKHIMHUZZ6OILYO3FVQW7IY';
        $goldenProgram = 'ASAHAegHZABfoMIevKOVASYCIAECAwQFBgcIAQIDBAUGBwgBAgMEBQYHCAECAwQFBgcIIJKvkYTkEzwJf2arzJOxERsSogG9nQzKPkpIoc4TzPTFMRAiEjEBIw4QMQIkGCUSEDEEIQQxAggSEDEGKBIQMQkyAxIxBykSEDEIIQUSEDEJKRIxBzIDEhAxAiEGDRAxCCUSEBEQ';
        $goldenTx = 'gqRsc2lngaFsxJkBIAcB6AdkAF+gwh68o5UBJgIgAQIDBAUGBwgBAgMEBQYHCAECAwQFBgcIAQIDBAUGBwggkq+RhOQTPAl/ZqvMk7ERGxKiAb2dDMo+SkihzhPM9MUxECISMQEjDhAxAiQYJRIQMQQhBDECCBIQMQYoEhAxCTIDEjEHKRIQMQghBRIQMQkpEjEHMgMSEDECIQYNEDEIJRIQERCjdHhuiaNhbXTOAAehIKNmZWXNA+iiZnbNBLCiZ2jEIH+DsWV/8fxTuS3BgUih1l38LUsfo9Z3KErd0gASbZBpomx2zQUPomx4xCABAgMEBQYHCAECAwQFBgcIAQIDBAUGBwgBAgMEBQYHCKNyY3bEIJKvkYTkEzwJf2arzJOxERsSogG9nQzKPkpIoc4TzPTFo3NuZMQgSyW1cXI76LA1KKwDMg39flXjnYOEuOUdDD0znzkLw7akdHlwZaNwYXk=';

        $receiver = Address::fromAlgorandAddress('SKXZDBHECM6AS73GVPGJHMIRDMJKEAN5TUGMUPSKJCQ44E6M6TC2H2UJ3I');
        $lease = 'AQIDBAUGBwgBAgMEBQYHCAECAwQFBgcIAQIDBAUGBwg=';

        // Create the contract
        $contract = PeriodicPayment::create($receiver, 500000, 95, 100, 1000, 2445756, $lease);

        $this->assertEquals($contract->getAddress()->encodedAddress, $goldenAddress);
        $this->assertEquals($contract->getProgram(), Base64::decode($goldenProgram));

        // Get the withdrawal transaction
        $gh = 'f4OxZX/x/FO5LcGBSKHWXfwtSx+j1ncoSt3SABJtkGk=';
        $signedTx = PeriodicPayment::getWithdrawalTransaction($contract, 1200, $gh, 0);
        $this->assertEquals($signedTx->toBase64(), $goldenTx);
    }
}
