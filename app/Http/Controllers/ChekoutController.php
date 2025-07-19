<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChekoutController extends Controller
{
    public function checkout(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['message' => 'aut ', 401]);
        }

        $cart = session('cart');

        if (!$cart || count($cart) === 0) {
            return response()->json(['message' => 'Keranjang Kosong!']);
        }

        // Validasi stok berdasarkan pembelian_detail (sum remaining_stock)
        foreach ($cart as $item) {
            $totalStock = DB::table('purchases_detail')
                ->where('product_id', $item['product_id'])
                ->sum('remaining_stock');

            if ($totalStock < $item['quantity']) {
                $product = Product::find($item['product_id']);
                return response()->json(['message' => "Stok {$product->name} tidak cukup"], 400);
            }
        }

        // Hitung total
        $total = collect($cart)->sum('subtotal');

        // Simpan transaksi sale
        $sale = Sale::create([
            'user_id' => $user->id,
            'customer_id' => $request->customer_id,
            'total' => $total,
            'paid_amount' => $request->paid_amount,
            'change_amount' => $request->paid_amount - $total,
            'payment_method' => $request->payment_method
        ]);

        // Kurangi stok di pembelian_detail (FIFO)
        foreach ($cart as $item) {
            $qtyToReduce = $item['quantity'];

            $details = DB::table('purchases_detail')
                ->where('product_id', $item['product_id'])
                ->where('remaining_stock', '>', 0)
                ->orderBy('created_at', 'asc')
                ->get();

            foreach ($details as $detail) {
                if ($qtyToReduce <= 0) break;

                if ($detail->remaining_stock >= $qtyToReduce) {
                    DB::table('purchases_detail')
                        ->where('id', $detail->id)
                        ->update(['remaining_stock' => $detail->remaining_stock - $qtyToReduce]);
                    $qtyToReduce = 0;
                } else {
                    $qtyToReduce -= $detail->remaining_stock;
                    DB::table('purchases_detail')
                        ->where('id', $detail->id)
                        ->update(['remaining_stock' => 0]);
                }
            }

            $product = Product::find($item['product_id']);
            // Simpan detail penjualan
            SaleDetails::create([
                'sale_id' => $sale->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => Product::find($item['product_id'])->price,
                'subtotal' => $product->price * $item['quantity']
            ]);
        }

        // Kosongkan keranjang
        session()->forget('cart');

        return response()->json(['message' => 'Transaksi Berhasil']);
    }
}
