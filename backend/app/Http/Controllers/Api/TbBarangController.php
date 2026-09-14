<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TbBarang;
use App\Models\TbKategori;
use App\Models\TbKelompokKategori;
use App\Models\TbSupplier;
use Illuminate\Http\Request;

class TbBarangController extends Controller
{
    public function index(Request $request)
    {
        $barang = TbBarang::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->orderBy('id_barang', 'desc')
        ->get();

        return response()->json($barang);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'barcode' => 'required|string|max:100',
            'nama' => 'required|string|max:255',
            'id_kategori' => 'required|integer',
            'id_kelompok_kategori' => 'required|integer',
            'id_supplier' => 'required|integer',
            'satuan' => 'required|string|max:50',
            'harga_beli' => 'required|numeric|min:0',
            'harga_jual' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'is_active' => 'required|boolean',
        ]);

        $sekolahId = $request->user()->id_sekolah;

        TbKategori::where('id_kategori', $validated['id_kategori'])
            ->whereHas('kelompokKategori', function ($query) use ($sekolahId) {
                $query->where('id_sekolah', $sekolahId);
            })
            ->firstOrFail();

        TbKelompokKategori::where(
            'id_kelompok',
            $validated['id_kelompok_kategori']
        )
        ->where('id_sekolah', $sekolahId)
        ->firstOrFail();

        TbSupplier::where(
            'id_supplier',
            $validated['id_supplier']
        )
        ->where('id_sekolah', $sekolahId)
        ->firstOrFail();

        $validated['id_sekolah'] = $sekolahId;
        $validated['created_by'] = $request->user()->id_user;

        $barang = TbBarang::create($validated);

        return response()->json([
            'message' => 'Barang berhasil ditambahkan',
            'data' => $barang
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $barang = TbBarang::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->findOrFail($id);

        return response()->json($barang);
    }

    public function update(Request $request, $id)
    {
        $barang = TbBarang::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->findOrFail($id);

        $validated = $request->validate([
            'barcode' => 'sometimes|string|max:100',
            'nama' => 'sometimes|string|max:255',
            'id_kategori' => 'sometimes|integer',
            'id_kelompok_kategori' => 'sometimes|integer',
            'id_supplier' => 'sometimes|integer',
            'satuan' => 'sometimes|string|max:50',
            'harga_beli' => 'sometimes|numeric|min:0',
            'harga_jual' => 'sometimes|numeric|min:0',
            'stok' => 'sometimes|integer|min:0',
            'is_active' => 'sometimes|boolean',
        ]);

        $sekolahId = $request->user()->id_sekolah;

        if (isset($validated['id_kategori'])) {
            TbKategori::where(
                'id_kategori',
                $validated['id_kategori']
            )
            ->whereHas('kelompokKategori', function ($query) use ($sekolahId) {
                $query->where('id_sekolah', $sekolahId);
            })
            ->firstOrFail();
        }

        if (isset($validated['id_kelompok_kategori'])) {
            TbKelompokKategori::where(
                'id_kelompok',
                $validated['id_kelompok_kategori']
            )
            ->where('id_sekolah', $sekolahId)
            ->firstOrFail();
        }

        if (isset($validated['id_supplier'])) {
            TbSupplier::where(
                'id_supplier',
                $validated['id_supplier']
            )
            ->where('id_sekolah', $sekolahId)
            ->firstOrFail();
        }

        $validated['updated_by'] = $request->user()->id_user;

        $barang->update($validated);

        return response()->json([
            'message' => 'Barang berhasil diubah',
            'data' => $barang
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $barang = TbBarang::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->findOrFail($id);

        $barang->deleted_by = $request->user()->id_user;
        $barang->is_delete = true;
        $barang->save();

        $barang->delete();

        return response()->json([
            'message' => 'Barang berhasil dihapus'
        ]);
    }
}