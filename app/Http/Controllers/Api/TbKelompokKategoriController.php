<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TbKelompokKategori;
use Illuminate\Http\Request;

class TbKelompokKategoriController extends Controller
{
    public function index(Request $request)
    {
        $kelompokKategori = TbKelompokKategori::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->orderBy('id_kelompok', 'desc')
        ->get();

        return response()->json($kelompokKategori);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelompok' => 'required|string|max:255',
        ]);

        $validated['id_sekolah'] = $request->user()->id_sekolah;
        $validated['created_by'] = $request->user()->id_user;

        $kelompokKategori = TbKelompokKategori::create($validated);

        return response()->json([
            'message' => 'Kelompok kategori berhasil ditambahkan',
            'data' => $kelompokKategori
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $kelompokKategori = TbKelompokKategori::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->findOrFail($id);

        return response()->json($kelompokKategori);
    }

    public function update(Request $request, $id)
    {
        $kelompokKategori = TbKelompokKategori::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->findOrFail($id);

        $validated = $request->validate([
            'nama_kelompok' => 'sometimes|string|max:255',
        ]);

        $validated['updated_by'] = $request->user()->id_user;

        $kelompokKategori->update($validated);

        return response()->json([
            'message' => 'Kelompok kategori berhasil diubah',
            'data' => $kelompokKategori
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $kelompokKategori = TbKelompokKategori::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->findOrFail($id);

        $kelompokKategori->deleted_by = $request->user()->id_user;
        $kelompokKategori->is_delete = true;
        $kelompokKategori->save();

        $kelompokKategori->delete();

        return response()->json([
            'message' => 'Kelompok kategori berhasil dihapus'
        ]);
    }
}