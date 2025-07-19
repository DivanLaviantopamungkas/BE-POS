<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\PembelianDetail;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PembelianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pembelians = Pembelian::with('supplier', 'detail')->get();

        return response()->json($pembelians);
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
        // Validasi data dari request
        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id', // supplier_id wajib dan harus ada di tabel suppliers
            'items' => 'required|array', // items wajib dan harus berupa array
            'items.*.product_id' => 'required|exists:products,id', // setiap item harus memiliki product_id yang valid
            'items.*.quantity' => 'required|integer|min:1', // quantity wajib, berupa integer, dan minimal 1
            'items.*.price' => 'required|integer|min:0', // price wajib, berupa integer, dan minimal 0
        ]);

        foreach ($validated['items'] as $item) {
            $item['reamining_stock'] = $item['quantity'];
        }
        unset($item);

        // Memulai transaksi database agar semua query disimpan sekaligus
        DB::beginTransaction();
        try {
            $total = 0;

            // Hitung total harga pembelian dari semua item
            foreach ($validated['items'] as $item) {
                $total += $item['quantity'] * $item['price'];
            }

            // Log untuk debugging, mencatat data yang akan dibuat
            Log::info('Data untuk Pembelian::create():', [
                'supplier_id' => $validated['supplier_id'],
                'total' => $total,
            ]);

            // Simpan data pembelian utama ke tabel pembelian
            $purchase = Pembelian::create([
                'supplier_id' => $validated['supplier_id'],
                'total' => $total,
            ]);

            // Simpan detail pembelian untuk setiap item ke tabel pembelian_detail
            foreach ($validated['items'] as $item) {
                PembelianDetail::create([
                    'purchase_id' => $purchase->id, // relasi ke tabel pembelian
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'remaining_stock' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'], // subtotal = quantity * price
                ]);
            }

            // Commit transaksi jika semua berhasil
            DB::commit();
            return response()->json(['message' => 'Pembelian berhasil'], 201);
        } catch (\Exception $e) {
            // Rollback transaksi jika terjadi error
            DB::rollBack();
            return response()->json(['message' => 'Gagal menyimpan pembelian', 'error' => $e->getMessage()], 500);
        }
    }



    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $purchase = Pembelian::with('supplier', 'detail')->find($id);
        if (!$purchase) {
            Log::warning("Pembelian ID {$id} tidak ditemukan");
            return response()->json(['message' => 'Pembelian tidak ditemukan'], 404);
        }
        Log::info('Pembelian data:', $purchase->toArray());
        Log::info('Detail data:', $purchase->detail->toArray());

        return response()->json($purchase);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id) {}

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            Log::info('Memulai validasi update pembelian');

            $validated = $request->validate([
                'supplier_id' => 'required|exists:suppliers,id',
                'items' => 'required|array',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|integer|min:0',
            ]);

            Log::info('Hasil validasi:', $validated);

            DB::beginTransaction();

            $purchase = Pembelian::findOrFail($id);

            $total = collect($validated['items'])->sum(fn($item) => $item['price'] * $item['quantity']);

            $purchase->update([
                'supplier_id' => $validated['supplier_id'],
                'total' => $total,
            ]);

            // Hapus detail lama
            PembelianDetail::where('purchase_id', $purchase->id)->delete();

            // Tambah detail baru
            foreach ($validated['items'] as $item) {
                PembelianDetail::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'remaining_stock' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['quantity'] * $item['price'],
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Berhasil diupdate'], 200);
        } catch (\Throwable $e) {
            Log::error('Gagal update pembelian', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            DB::rollBack();
            return response()->json(['message' => 'Gagal update', 'error' => $e->getMessage()], 500);
        }
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pembelian = Pembelian::findOrFail($id);
        $pembelian->detail()->delete(); // hapus detail dulu
        $pembelian->delete();

        return response()->json(['message' => 'Pembelian berhasil dihapus']);
    }
}
