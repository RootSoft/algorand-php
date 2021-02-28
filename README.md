<p align="center"> 
<img src="https://miro.medium.com/max/700/1*BFpFCJepifaREIg7qLSLag.jpeg">
</p>

# algorand-php
[![Packagist][packagist-shield]][packagist-url]
[![Downloads][downloads-shield]][downloads-url]
[![Issues][issues-shield]][issues-url]
[![MIT License][license-shield]][license-url]

Algorand is a public blockchain and protocol that aims to deliver decentralization, scale and security for all participants.
Their PURE PROOF OF STAKE™ consensus mechanism ensures full participation, protection, and speed within a truly decentralized network. With blocks finalized in seconds, Algorand’s transaction throughput is on par with large payment and financial networks. And Algorand is the first blockchain to provide immediate transaction finality. No forking. No uncertainty. 


## Introduction
Algorand-php is a community SDK with an elegant approach to connect your application to the Algorand blockchain, send transactions, create assets and query the indexer with just a few lines of code.

Once installed, you can simply connect your application to the blockchain and start sending payments

```php
$algorand->sendPayment($account, $recipient, Algo::toMicroAlgos(10), 'Hi');
```

or create a new asset:

```php
$algorand->assetManager()->createNewAsset($account, 'PHPCoin', 'PHP', 500000, 2);
```

## Features
* Algod
* Indexer
* Transactions
* Account management
* Asset management
* TEAL compilation
* Laravel support :heart:

## Getting started

### Installation
> **Note**: Algorand-php requires PHP 7.4+

You can install the package via composer:

```bash
composer require rootsoft/algorand-php
```

## Usage
Create an ```AlgodClient``` and ```IndexerClient``` and pass them to the ```Algorand``` constructor.
We added extra support for locally hosted nodes & third party services (like PureStake).

```php
$algodClient = new AlgodClient(PureStake::MAINNET_ALGOD_API_URL, 'YOUR-API-KEY');
$indexerClient = new IndexerClient(PureStake::MAINNET_INDEXER_API_URL, 'YOUR-API-KEY');
$algorand = new Algorand($algodClient, $indexerClient);
```

### Laravel :heart:
We've added special support to make the life of a Laravel developer even more easy!

Publish the ```algorand.php``` config file using:
```
php artisan vendor:publish --provider="Rootsoft\Algorand\AlgorandServiceProvider" --tag="config"
```

Open the ```config/algorand.php``` file in your project and insert your credentials
```php
return [
    'algod' => [
        'api_url' => 'https://testnet-algorand.api.purestake.io/ps2',
        'api_key' => 'YOUR API KEY',
        'api_token_header' => 'x-api-key',
    ],
    'indexer' => [
        'api_url' => 'https://testnet-algorand.api.purestake.io/idx2',
        'api_key' => 'YOUR API KEY',
        'api_token_header' => 'x-api-key',
    ],
];
```

Now you can use the ```Algorand``` Facade!

```php
Algorand::sendPayment($account, $recipient, Algo::toMicroAlgos(10), 'Hi');
```

## Account Management
Accounts are entities on the Algorand blockchain associated with specific onchain data, like a balance. An Algorand Address is the identifier for an Algorand account.
You can use the ```AccountManager``` to perform all account related tasks.

### Creating a new account

Creating a new account is as easy as calling:
```php
$account = $algorand->accountManager()->createNewAccount();
```

With the given account, you can easily extract the public Algorand address, signing keys and seedphrase/mnemonic.
```php
$address = $account->getPublicAddress();
$seedphrase = $account->getSeedPhrase();
```

### Loading an existing account

You can load an existing account using your **generated secret key or binary seed**.

```php
$algorand->accountManager()->loadAccountFromSecret('secret key');
$algorand->accountManager()->loadAccountFromSeed(hex2bin($seed));
```

### Restoring an account

Recovering an account from your 25-word mnemonic/seedphrase can be done by passing an **array or space delimited string**

```php
$account = Algorand::accountManager()->restoreAccount($seedphrase);
```

## Transactions
There are multiple ways to create a transaction. We've included helper functions to make our life easier.

```php
$algorand->sendPayment($account, $recipient, Algo::toMicroAlgos(10), 'Hi');
```

Or you can use the ```TransactionBuilder``` to create more specific, raw transactions:

```php
// Create a new transaction
$transaction = TransactionBuilder::payment()
    ->sender($account->getAddress())
    ->note('Algonauts assemble!')
    ->amount(Algo::toMicroAlgos(1.2)) // 5 Algo
    ->receiver($recipient)
    ->useSuggestedParams(Algorand::client())
    ->suggestedFeePerByte(10)
    ->build();

/// Sign the transaction
$signedTransaction = $transaction->sign($account);

// Send the transaction
$transactionId = $algorand->sendTransaction($signedTransaction);
```

## Asset Management

**Create a new asset**

Creating a new asset is as simple as using the ```AssetManager``` included in the Algorand SDK:

```php
$algorand->assetManager()->createNewAsset($account, 'Laracoin', 'LARA', 500000, 2);
```

Or as usual, you can use the ```TransactionBuilder``` to create your asset:

```php
// Create a new asset
$transaction = TransactionBuilder::assetConfig()
    ->assetName($assetName)
    ->unitName($unitName)
    ->totalAssetsToCreate(BigInteger::of($totalAssets))
    ->decimals($decimals)
    ->defaultFrozen($defaultFrozen)
    ->managerAddress($managerAddress)
    ->reserveAddress($reserveAddress)
    ->freezeAddress($freezeAddress
    ->clawbackAddress($clawbackAddress )
    ->sender($address)
    ->suggestedParams($params)
    ->build();

// Sign the transaction
$signedTransaction = $transaction->sign($account);

// Broadcast the transaction on the network
$algorand->sendTransaction($signedTransaction);
```

**Edit an asset**

After an asset has been created only the manager, reserve, freeze and clawback accounts can be changed.
All other parameters are locked for the life of the asset.

If any of these addresses are set to "" that address will be cleared and can never be reset for the life of the asset.
Only the manager account can make configuration changes and must authorize the transaction.

```php
$algorand->assetManager()->editAsset(14192345, $account, $newAccount->getAddress());
```

**Destroy an asset**

```php
$algorand->assetManager()->destroyAsset(14192345, $account);
```

**Opt in to receive an asset**

Before being able to receive an asset, you should opt in
An opt-in transaction is simply an asset transfer with an amount of 0, both to and from the account opting in.
Assets can be transferred between accounts that have opted-in to receiving the asset.

```php
$algorand->assetManager()->optIn(14192345, $newAccount);
```

**Transfer an asset**

Transfer an asset from the account to the receiver.
Assets can be transferred between accounts that have opted-in to receiving the asset.
These are analogous to standard payment transactions but for Algorand Standard Assets.

```php
$algorand->assetManager()->transfer(14192345, $account, 1000, $newAccount->getAddress());
```

**Freeze an asset**

Freezing or unfreezing an asset requires a transaction that is signed by the freeze account.

Upon creation of an asset, you can specify a freeze address and a defaultfrozen state.
If the defaultfrozen state is set to true the corresponding freeze address must issue unfreeze transactions,
to allow trading of the asset to and from that account.
This may be useful in situations that require holders of the asset to pass certain checks prior to ownership.

```php
$algorand->assetManager()->freeze(14192345, $account, $newAccount->getAddress(), false);
```

**Revoking an asset**

Revoking an asset for an account removes a specific number of the asset from the revoke target account.
Revoking an asset from an account requires specifying an asset sender (the revoke target account) and an
asset receiver (the account to transfer the funds back to).

```php
$algorand->assetManager()->revoke(14192345, $account, 1000, $newAccount->getAddress());
```

## Indexer
Algorand provides a standalone daemon algorand-indexer that reads committed blocks from the Algorand blockchain and
maintains a local database of transactions and accounts that are searchable and indexed.

The PHP SDK makes it really easy to search the ledger in a fluent api and enables application developers to perform rich and efficient queries on accounts,
transactions, assets, and so forth.

At the moment we support queries on transactions, assets and accounts.

### Transactions

```php
$algorand->indexer()
    ->transactions()
    ->whereCurrencyIsLessThan(Algo::toMicroAlgos(1000))
    ->whereCurrencyIsGreaterThan(Algo::toMicroAlgos(500))
    ->whereAssetId(14502)
    ->whereNotePrefix('PHP')
    ->whereTransactionType(TransactionType::PAYMENT())
    ->search();
```

### Assets

```php
$algorand->indexer()
    ->assets()
    ->whereUnitName('PHP')
    ->whereAssetName('PHPCoin')
    ->whereCurrencyIsLessThan(Algo::toMicroAlgos(1000))
    ->whereCurrencyIsGreaterThan(Algo::toMicroAlgos(500))
    ->whereAssetId(14502)
    ->search();
```
### Accounts

```php
Algorand::indexer()
    ->accounts()
    ->whereAssetId(15205)
    ->whereAuthAddress('RQM43TQH4CHTOXKPLDWVH4FUZQVOWYHRXATHJSQLF7GN6CFFLC35FLNYHM')
    ->limit(5)
    ->search();
```
## Roadmap
* Better support for Big Integers
* Participation in consensus
* KMD
* Smart contracts
* Authorization & rekeying
* Tests

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing & Pull Requests
Feel free to send pull requests.

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Credits

- [Tomas Verhelst](https://github.com/rootsoft)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.


<!-- MARKDOWN LINKS & IMAGES -->
<!-- https://www.markdownguide.org/basic-syntax/#reference-style-links -->
[packagist-shield]: https://img.shields.io/packagist/v/rootsoft/algorand-php.svg?style=for-the-badge
[packagist-url]: https://packagist.org/packages/rootsoft/algorand-php
[downloads-shield]: https://img.shields.io/packagist/dt/rootsoft/algorand-php.svg?style=for-the-badge
[downloads-url]: https://packagist.org/packages/rootsoft/algorand-php
[issues-shield]: https://img.shields.io/github/issues/rootsoft/algorand-php.svg?style=for-the-badge
[issues-url]: https://github.com/rootsoft/algorand-php/issues
[license-shield]: https://img.shields.io/github/license/rootsoft/algorand-php.svg?style=for-the-badge
[license-url]: https://github.com/rootsoft/algorand-php/blob/master/LICENSE.txt
