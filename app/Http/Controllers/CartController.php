<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

use function Pest\Laravel\put;

class CartController extends Controller
{
    // Mengambil data keranjang dari session, jika tidak ada kembalikan array kosong
    public function getCart()
    {
        $cart = session()->get('cart', []);

        // Ambil semua product_id yang ada di cart
        $productIds = array_column($cart, 'product_id');

        // Query produk lengkap sekaligus atribut stock
        $products = Product::whereIn('id', $productIds)->get();

        // Gabungkan data produk dengan qty dan subtotal dari session cart
        $fullCart = collect($cart)->map(function ($item) use ($products) {
            $product = $products->firstWhere('id', $item['product_id']);
            return [
                'product_id' => $item['product_id'],
                'name' => $product->name,
                'price' => $product->price,
                'quantity' => $item['quantity'],
                'subtotal' => $item['subtotal'],
                'stock' => $product->stock, // atribut dari accessor kamu
            ];
        });

        return response()->json($fullCart->values());
    }


    // Menambahkan produk ke keranjang
    public function addToCart(Request $request)
    {
        $product = Product::findOrFail($request->product_id);

        $cart = session()->get('cart', []);

        // Buat ulang cart jadi array numerik agar aman
        $cart = array_values($cart);

        // Cari index berdasarkan product_id
        $index = collect($cart)->search(fn($item) => isset($item['product_id']) && $item['product_id'] == $request->product_id);

        if ($index !== false && isset($cart[$index])) {
            $cart[$index]['quantity'] += 1;
            $cart[$index]['subtotal'] = $cart[$index]['quantity'] * $product->price;
        } else {
            $cart[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'quantity' => 1,
                'subtotal' => $product->price
            ];
        }

        session()->put('cart', $cart);

        info('Keranjang berhasil disimpan ke session.');
        info('Cart session content: ' . print_r(session('cart'), true));

        return response()->json(['message' => 'Produk Berhasil ditambahkan ke keranjang']);
    }

    // Menghapus produk dari keranjang berdasarkan product_id
    public function removeCart(Request $request)
    {
        // Ambil data keranjang dari session
        $cart = session()->get('cart', []);

        // Filter produk yang product_id-nya sama dengan product_id di request supaya dihapus
        // Catatan: ada typo yang harus diperbaiki (lihat catatan di bawah)
        $filtered = collect($cart)->reject(fn($item) => $item['product_id'] == $request->product_id);

        // Simpan keranjang yang sudah difilter kembali ke session
        session()->put('cart', $filtered->values()->all()); // pakai values() untuk reset index array

        // Berikan response sukses
        return response()->json(['message' => 'Produk dihapus dari keranjang']);
    }

    public function updateCart(Request $request)
    {
        $cart = $request->input('cart');
        Log::info('Request masuk ke updateCart:', ['cart' => $cart]);
        if (!is_array($cart)) {
            return response()->json(['messagge' => 'invalid cart array']);
        }

        session(['cart' => $cart]);
        session()->save();

        return response()->json(['message' => 'cart session update']);
    }

    // Mengosongkan seluruh keranjang
    public function clearCart()
    {
        // Hapus data cart di session
        session()->forget('cart');

        // Berikan response sukses
        return response()->json(['message' => 'Keranjang dikosongkan']);
    }
}
