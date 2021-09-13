<?php

namespace Rootsoft\Algorand\Tests\Unit\Teal;

use Orchestra\Testbench\TestCase;
use ParagonIE\ConstantTime\Base64;
use Rootsoft\Algorand\Models\Accounts\Account;
use Rootsoft\Algorand\Models\Accounts\Address;
use Rootsoft\Algorand\Models\Applications\TEALProgram;
use Rootsoft\Algorand\Utils\CryptoUtils;

class TealSignTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function testTEALSign()
    {
        $data = Base64::decode('Ux8jntyBJQarjKGF8A==');
        $seed = Base64::decode('5Pf7eGMA52qfMT4R4/vYCt7con/7U3yejkdXkrcb26Q=');
        $program = new TEALProgram(Base64::decode('ASABASI='));
        $address = Address::fromAlgorandAddress('6Z3C3LDVWGMX23BMSYMANACQOSINPFIRF77H7N3AWJZYV6OH6GWTJKVMXY');
        $account = Account::seed($seed);

        $sig1 = $address->sign($account, $data);
        $sig2 = $program->sign($account, $data);
        $this->assertEquals($sig1, $sig2);

        $buffer = utf8_encode(TEALProgram::PROGDATA_SIGN_PREFIX);
        $buffer .= $address->address;
        $buffer .= $data;

        $verified = CryptoUtils::verify($buffer, $sig1->bytes(), $account->getPublicKey());
        $this->assertTrue($verified);
    }
}
