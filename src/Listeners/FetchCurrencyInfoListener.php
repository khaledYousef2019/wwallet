<?php

namespace App\Listeners;

use App\Events\FetchCurrencyInfo;
use GuzzleHttp\Client;

class FetchCurrencyInfoListener
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://api.coingecko.com/api/v3/']);
    }

    public function handle(FetchCurrencyInfo $event): void
    {
        $response = $this->client->get('coins/markets', [
            'query' => [
                'vs_currency' => 'usd',
                'ids' => $event->coinAddress,
            ]
        ]);

        $data = json_decode($response->getBody(), true);
        // Process the fetched data here
        // For example, you can log it or store it in the database
    }
}
?>
