<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TbKategori;
use App\Models\TbKelompokKategori;
use Illuminate\Http\Request;

class TbKategoriController extends Controller
{
    public function index(Request $request)
    {
        $kategori = TbKategori::whereHas('kelompokKategori', function ($query) use ($request) {
            $query->where('id_sekolah', $request->user()->id_sekolah);
        })
        ->orderBy('id_kategori', 'desc')
        ->get();

        return response()->json($kategori);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_kelompok' => 'required|integer',
            'nama' => 'required|string|max:255',
        ]);

        TbKelompokKategori::where(
            'id_kelompok',
            $validated['id_kelompok']
        )
        ->where('id_sekolah', $request->user()->id_sekolah)
        ->firstOrFail();

        $validated['created_by'] = $request->user()->id_user;

        $kategori = TbKategori::create($validated);

        return response()->json([
            'message' => 'Kategori berhasil ditambahkan',
            'data' => $kategori
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $kategori = TbKategori::whereHas('kelompokKategori', function ($query) use ($request) {
            $query->where('id_sekolah', $request->user()->id_sekolah);
        })
        ->findOrFail($id);

        return response()->json($kategori);
    }

    public function update(Request $request, $id)
    {
        $kategori = TbKategori::whereHas('kelompokKategori', function ($query) use ($request) {
            $query->where('id_sekolah', $request->user()->id_sekolah);
        })
        ->findOrFail($id);

        $validated = $request->validate([
            'id_kelompok' => 'sometimes|integer',
            'nama' => 'sometimes|string|max:255',
        ]);

        if (isset($validated['id_kelompok'])) {
            TbKelompokKategori::where(
                'id_kelompok',
                $validated['id_kelompok']
            )
            ->where('id_sekolah', $request->user()->id_sekolah)
            ->firstOrFail();
        }

        $validated['updated_by'] = $request->user()->id_user;

        $kategori->update($validated);

        return response()->json([
            'message' => 'Kategori berhasil diubah',
            'data' => $kategori
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $kategori = TbKategori::whereHas('kelompokKategori', function ($query) use ($request) {
            $query->where('id_sekolah', $request->user()->id_sekolah);
        })
        ->findOrFail($id);

        $kategori->deleted_by = $request->user()->id_user;
        $kategori->is_delete = true;
        $kategori->save();

        $kategori->delete();

        return response()->json([
            'message' => 'Kategori berhasil dihapus'
        ]);
    }
}