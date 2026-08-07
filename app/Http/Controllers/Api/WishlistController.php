<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request)
    {
        $wishlists = $request->user()
            ->wishlists()
            ->with('product.category')
            ->latest()
            ->get();

        return response()->json($wishlists);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);
        
        $existing = $request->user()
            ->wishlists()
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Produk sudah ada di wishlist'], 422);
        }

        $wishlist = $request->user()->wishlists()->create($validated);

        return response()->json($wishlist->load('product'), 201);
    }

    public function destroy(Request $request, Wishlist $wishlist)
    {
        if ($wishlist->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $wishlist->delete();

        return response()->json(['message' => 'Produk berhasil dihapus dari wishlist']);
    }

    public function toggle(Request $request)
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
        ]);

        $existing = $request->user()
            ->wishlists()
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['message' => 'Dihapus dari wishlist', 'wishlisted' => false]);
        }

        $wishlist = $request->user()->wishlists()->create($validated);
        return response()->json(['message' => 'Ditambahkan ke wishlist', 'wishlisted' => true, 'data' => $wishlist->load('product')]);
    }
}
