<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TbKelompokPelanggan;
use Illuminate\Http\Request;

class TbKelompokPelangganController extends Controller
{
    public function index(Request $request)
    {
        $kelompok = TbKelompokPelanggan::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->orderBy('id_kelompok_pelanggan', 'desc')
        ->get();

        return response()->json($kelompok);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelompok' => 'required|string|max:50',
        ]);

        $validated['id_sekolah'] = $request->user()->id_sekolah;

        $kelompok = TbKelompokPelanggan::create($validated);

        return response()->json([
            'message' => 'Kelompok pelanggan berhasil ditambahkan',
            'data' => $kelompok,
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $kelompok = TbKelompokPelanggan::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->findOrFail($id);

        return response()->json($kelompok);
    }

    public function update(Request $request, $id)
    {
        $kelompok = TbKelompokPelanggan::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->findOrFail($id);

        $validated = $request->validate([
            'nama_kelompok' => 'sometimes|string|max:50',
        ]);

        $kelompok->update($validated);

        return response()->json([
            'message' => 'Kelompok pelanggan berhasil diubah',
            'data' => $kelompok,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $kelompok = TbKelompokPelanggan::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->findOrFail($id);

        $kelompok->delete();

        return response()->json([
            'message' => 'Kelompok pelanggan berhasil dihapus',
        ]);
    }
}