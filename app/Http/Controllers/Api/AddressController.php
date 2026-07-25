<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Address\StoreAddressRequest;
use App\Http\Requests\Address\UpdateAddressRequest;
use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request)
    {
        return response()->json(
            $request->user()->addresses()->latest()->get()
        );
    }

    public function store(StoreAddressRequest $request)
    {
        $address = $request->user()->addresses()->create($request->validated());

        return response()->json($address, 201);
    }

    public function show(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        return response()->json($address);
    }

    public function update(UpdateAddressRequest $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        $address->update($request->validated());

        return response()->json($address);
    }

    public function destroy(Request $request, Address $address)
    {
        if ($address->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Akses ditolak'], 403);
        }

        // Cek dulu apakah alamat ini masih dipakai di order manapun
        if ($address->orders()->exists()) {
            return response()->json([
                'message' => 'Alamat tidak bisa dihapus karena masih terkait dengan riwayat order',
            ], 422);
        }

        $address->delete();

        return response()->json(['message' => 'Alamat berhasil dihapus']);
    }
}
