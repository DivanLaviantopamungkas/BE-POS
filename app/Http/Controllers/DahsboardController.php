<?php

namespace App\Http\Controllers;

use App\Models\PembelianDetail;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class DahsboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getSummary()
    {
        $today = now()->toDateString();

        $totalPenjualan = Sale::sum('total');
        $totalPenjualanHariIni = Sale::whereDate('created_at', $today)->count();
        $totalProduk = PembelianDetail::count();
        $stokHampirHabis = PembelianDetail::where('quantity', '<=', 5)->count();

        return response()->json([
            'totalPenjualan' => $totalPenjualan,
            'totalPenjualanHariIni' => $totalPenjualanHariIni,
            'totalProduk' => $totalProduk,
            'stock' => $stokHampirHabis
        ]);
    }

    public function getSalesPerMonth()
    {
        return Sale::selectRaw('MONTH(created_at) as month, SUM(paid_amount) as totals')
            ->groupByRaw('MONTH(created_at)')
            ->orderByRaw('MONTH(created_at)')
            ->get();
    }

    public function getRecentTransaction($limit = 0)
    {
        if ($limit == 0) {
            $limit = 5;
        }

        $sales = Sale::with(['user', 'customer'])
            ->orderBy('created_at', 'desc')
            ->take($limit)
            ->get()
            ->map(function ($sale) {
                return [
                    'id' => $sale->id,
                    'tanggal' => $sale->created_at->format('Y-m-d'),
                    'kasir' => $sale->user->name,
                    'pelanggan' => $sale->customer->name ?? '-',
                    'metode' => $sale->payment_method,
                    'total' => $sale->paid_amount,
                    'status' => $sale->paid_amount >= $sale->total ? 'lunas' : 'Belum Lunas'
                ];
            });
        Log::info('Data Transaksi Terbaru:', ['sales' => $sales]);

        return $sales;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
