<?php

namespace App\Http\Controllers;

use App\Models\Cryptocurrency;
use App\Models\PriceHistory;
use App\Services\CoinMarketCapService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CryptoController extends Controller
{
    private CoinMarketCapService $coinMarketCapService;

    public function __construct(CoinMarketCapService $coinMarketCapService)
    {
        $this->coinMarketCapService = $coinMarketCapService;
    }

    /**
     * Listar criptomonedas trackeadas
     */
    public function index()
    {
        $cryptos = Cryptocurrency::tracked()
            ->with('latestPrice')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $cryptos,
        ]);
    }

    /**
     * Buscar criptomonedas
     */
    public function search(Request $request)
    {
        $query = $request->input('q', '');

        if (strlen($query) < 2) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $results = $this->coinMarketCapService->searchCryptocurrencies($query);

        return response()->json([
            'success' => true,
            'data' => array_slice($results, 0, 20),
        ]);
    }

    /**
     * Agregar criptomoneda al seguimiento
     */
    public function track(Request $request)
    {
        $request->validate([
            'coin_id' => 'required|string',
            'symbol' => 'required|string',
            'name' => 'required|string',
        ]);

        $crypto = Cryptocurrency::firstOrCreate(
            ['coin_id' => $request->coin_id],
            [
                'symbol' => strtoupper($request->symbol),
                'name' => $request->name,
                'is_tracked' => true,
            ]
        );

        if (!$crypto->is_tracked) {
            $crypto->update(['is_tracked' => true]);
        }

        return response()->json([
            'success' => true,
            'data' => $crypto,
            'message' => 'Criptomoneda agregada al seguimiento',
        ]);
    }

    /**
     * Eliminar del seguimiento
     */
    public function destroy($id)
    {
        $crypto = Cryptocurrency::findOrFail($id);
        $crypto->update(['is_tracked' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Criptomoneda removida del seguimiento',
        ]);
    }

    /**
     * Obtener precios actuales de las criptomonedas trackeadas
     */
    public function getPrices()
    {
        $trackedCryptos = Cryptocurrency::tracked()->get();

        if ($trackedCryptos->isEmpty()) {
            return response()->json([
                'success' => true,
                'data' => [],
            ]);
        }

        $symbols = $trackedCryptos->pluck('symbol')->toArray();
        $quotes = $this->coinMarketCapService->getQuotes($symbols);

        $prices = [];
        foreach ($trackedCryptos as $crypto) {
            $quoteData = $quotes[$crypto->symbol][0] ?? null;

            if ($quoteData) {
                $usdQuote = $quoteData['quote']['USD'];

                // Guardar en historial
                PriceHistory::create([
                    'cryptocurrency_id' => $crypto->id,
                    'price_usd' => $usdQuote['price'],
                    'percent_change_1h' => $usdQuote['percent_change_1h'] ?? null,
                    'percent_change_24h' => $usdQuote['percent_change_24h'] ?? null,
                    'percent_change_7d' => $usdQuote['percent_change_7d'] ?? null,
                    'market_cap' => $usdQuote['market_cap'] ?? null,
                    'volume_24h' => $usdQuote['volume_24h'] ?? null,
                    'recorded_at' => Carbon::now(),
                ]);

                $prices[] = [
                    'id' => $crypto->id,
                    'symbol' => $crypto->symbol,
                    'name' => $crypto->name,
                    'price' => $usdQuote['price'],
                    'percent_change_1h' => $usdQuote['percent_change_1h'] ?? 0,
                    'percent_change_24h' => $usdQuote['percent_change_24h'] ?? 0,
                    'percent_change_7d' => $usdQuote['percent_change_7d'] ?? 0,
                    'market_cap' => $usdQuote['market_cap'] ?? 0,
                    'volume_24h' => $usdQuote['volume_24h'] ?? 0,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $prices,
            'timestamp' => Carbon::now()->toIso8601String(),
        ]);
    }
}
