<?php

namespace Rootsoft\Algorand;

use Illuminate\Support\ServiceProvider;
use Rootsoft\Algorand\Clients\AlgodClient;
use Rootsoft\Algorand\Clients\AlgoExplorer;
use Rootsoft\Algorand\Clients\IndexerClient;
use Rootsoft\Algorand\Clients\KmdClient;

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
            $algodUrl = config('algorand.algod.api_url', AlgoExplorer::TESTNET_ALGOD_API_URL);
            $algodApiKey = config('algorand.algod.api_key', '');
            $algodTokenHeader = config('algorand.algod.api_token_header', AlgodClient::ALGOD_API_TOKEN);

            $indexerUrl = config('algorand.indexer.api_url', AlgoExplorer::TESTNET_INDEXER_API_URL);
            $indexerApiKey = config('algorand.indexer.api_key', '');
            $indexerTokenHeader = config('algorand.indexer.api_token_header', IndexerClient::INDEXER_API_TOKEN);

            $kmdUrl = config('algorand.kmd.api_url', '127.0.0.1');
            $kmdApiKey = config('algorand.kmd.api_key', '');
            $kmdTokenHeader = config('algorand.kmd.api_token_header', KmdClient::KMD_API_TOKEN);

            $algodClient = new AlgodClient($algodUrl, $algodApiKey, $algodTokenHeader);
            $indexerClient = new IndexerClient($indexerUrl, $indexerApiKey, $indexerTokenHeader);
            $kmdClient = new KmdClient($kmdUrl, $kmdApiKey, $kmdTokenHeader);

            return new Algorand($algodClient, $indexerClient, $kmdClient);
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
