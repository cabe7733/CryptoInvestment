<?php
namespace App\Http\Controllers;

use App\Models\Cryptocurrency;
use App\Models\PriceHistory;
use Illuminate\Http\Request;
use Carbon\Carbon;

class HistoryController extends Controller
{
    /**
     * Obtener historial completo de una criptomoneda
     */
    public function show($id)
    {
        $crypto = Cryptocurrency::findOrFail($id);

        $history = PriceHistory::where('cryptocurrency_id', $id)
            ->orderBy('recorded_at', 'desc')
            ->limit(1000)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'cryptocurrency' => $crypto,
                'history' => $history,
            ],
        ]);
    }

    /**
     * Obtener historial en rango de fechas
     */
    public function getRange(Request $request, $id)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $crypto = Cryptocurrency::findOrFail($id);

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $history = PriceHistory::where('cryptocurrency_id', $id)
            ->inDateRange($startDate, $endDate)
            ->orderBy('recorded_at', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'cryptocurrency' => $crypto,
                'history' => $history,
                'range' => [
                    'start' => $startDate->toIso8601String(),
                    'end' => $endDate->toIso8601String(),
                ],
            ],
        ]);
    }
}
