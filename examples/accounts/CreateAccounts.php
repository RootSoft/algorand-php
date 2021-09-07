<?php
require_once('../../vendor/autoload.php');

use Rootsoft\Algorand\Models\Accounts\Account;

class CreateAccounts
{
    public static function main()
    {
        $account = Account::random();
        prettyPrint("My address 1: " . $account->getPublicAddress());
        prettyPrint("My passphrase 1: " . implode(' ', $account->getSeedPhrase()->words));

        $account = Account::random();
        prettyPrint("My address 2: " . $account->getPublicAddress());
        prettyPrint("My passphrase 2: " . implode(' ', $account->getSeedPhrase()->words));

        $account = Account::random();
        prettyPrint("My address 3: " . $account->getPublicAddress());
        prettyPrint("My passphrase 3: " . implode(' ', $account->getSeedPhrase()->words));
    }
}

CreateAccounts::main();

function prettyPrint($data)
{
    echo $data . PHP_EOL;
}
