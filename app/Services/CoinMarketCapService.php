<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CoinMarketCapService
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->baseUrl = 'https://pro-api.coinmarketcap.com/v1';
        $this->apiKey = config('services.coinmarketcap.api_key');
    }

    /**
     * Obtener lista de criptomonedas
     */
    public function getListings(int $limit = 100)
    {
        try {
            $response = Http::withHeaders([
                'X-CMC_PRO_API_KEY' => $this->apiKey,
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/cryptocurrency/listings/latest", [
                'limit' => $limit,
                'convert' => 'USD',
            ]);

            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }

            Log::error('CoinMarketCap API Error', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('CoinMarketCap Service Exception', [
                'message' => $e->getMessage(),
            ]);
            return [];
        }
    }

    /**
     * Obtener cotización de criptomonedas específicas
     */
    public function getQuotes(array $symbols)
    {
        if (empty($symbols)) {
            return [];
        }

        try {
            $symbolString = implode(',', $symbols);

            $response = Http::withHeaders([
                'X-CMC_PRO_API_KEY' => $this->apiKey,
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/cryptocurrency/quotes/latest", [
                'symbol' => $symbolString,
                'convert' => 'USD',
            ]);

            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }

            Log::error('CoinMarketCap Quotes Error', [
                'status' => $response->status(),
                'symbols' => $symbolString,
            ]);

            return [];
        } catch (\Exception $e) {
            Log::error('CoinMarketCap Quotes Exception', [
                'message' => $e->getMessage(),
                'symbols' => $symbols,
            ]);
            return [];
        }
    }

    /**
     * Buscar criptomonedas por término
     */
    public function searchCryptocurrencies(string $query)
    {
        $cacheKey = "crypto_search_{$query}";

        return Cache::remember($cacheKey, 300, function () use ($query) {
            try {
                $response = Http::withHeaders([
                    'X-CMC_PRO_API_KEY' => $this->apiKey,
                    'Accept' => 'application/json',
                ])->get("{$this->baseUrl}/cryptocurrency/map");

                if ($response->successful()) {
                    $allCryptos = $response->json()['data'] ?? [];

                    return array_filter($allCryptos, function ($crypto) use ($query) {
                        return stripos($crypto['name'], $query) !== false ||
                               stripos($crypto['symbol'], $query) !== false;
                    });
                }

                return [];
            } catch (\Exception $e) {
                Log::error('CoinMarketCap Search Exception', [
                    'message' => $e->getMessage(),
                    'query' => $query,
                ]);
                return [];
            }
        });
    }

    /**
     * Obtener información de una criptomoneda
     */
    public function getCryptoInfo(int $coinId)
    {
        try {
            $response = Http::withHeaders([
                'X-CMC_PRO_API_KEY' => $this->apiKey,
                'Accept' => 'application/json',
            ])->get("{$this->baseUrl}/cryptocurrency/info", [
                'id' => $coinId,
            ]);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                return $data[$coinId] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('CoinMarketCap Info Exception', [
                'message' => $e->getMessage(),
                'coin_id' => $coinId,
            ]);
            return null;
        }
    }
}
