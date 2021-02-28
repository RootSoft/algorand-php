<?php


namespace Rootsoft\Algorand;

use Illuminate\Support\ServiceProvider;
use Rootsoft\Algorand\Clients\AlgodClient;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Clients\PureStake;

class AlgorandServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerPublishables();

    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/algorand.php', 'algorand');

        $this->app->singleton(Algorand::class, function ($app) {
            $algodApiUrl = config('algorand.algod.api_url', PureStake::TESTNET_ALGOD_API_URL);
            $algodApiKey = config('algorand.algod.api_key', '');
            $algodApiTokenHeader = config('algorand.algod.api_token_header', PureStake::API_TOKEN_HEADER);

            $indexerApiUrl = config('algorand.indexer.api_url', PureStake::TESTNET_ALGOD_API_URL);
            $indexerApiKey = config('algorand.indexer.api_key', '');
            $indexerApiTokenHeader = config('algorand.indexer.api_token_header', PureStake::API_TOKEN_HEADER);

            $algodClient = new AlgodClient($algodApiUrl, $algodApiKey, $algodApiTokenHeader);
            $indexerClient = new IndexerClient($indexerApiUrl, $indexerApiKey, $indexerApiTokenHeader);

            return new Algorand($algodClient, $indexerClient);
        });
    }

    protected function registerPublishables()
    {
        // php artisan vendor:publish --provider="Rootsoft\Algorand\AlgorandServiceProvider" --tag="config"
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/algorand.php' => config_path('algorand.php'),
            ], 'config');
        }
    }
}
