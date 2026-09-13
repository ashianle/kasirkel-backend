<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TbPelanggan;
use App\Models\TbKelompokPelanggan;
use Illuminate\Http\Request;

class TbPelangganController extends Controller
{
    public function index(Request $request)
    {
        $pelanggan = TbPelanggan::whereHas('kelompokPelanggan', function ($query) use ($request) {
            $query->where(
                'id_sekolah',
                $request->user()->id_sekolah
            );
        })
        ->with('kelompokPelanggan')
        ->orderBy('id_pelanggan', 'desc')
        ->get();

        return response()->json($pelanggan);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_kelompok_pelanggan' => 'required|integer',
            'nama_pelanggan' => 'required|string|max:255',
            'telepon' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
        ]);

        $kelompok = TbKelompokPelanggan::where(
            'id_kelompok_pelanggan',
            $validated['id_kelompok_pelanggan']
        )
        ->where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->firstOrFail();

        $validated['created_by'] = $request->user()->id_user;

        $pelanggan = TbPelanggan::create($validated);

        return response()->json([
            'message' => 'Pelanggan berhasil ditambahkan',
            'data' => $pelanggan
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $pelanggan = TbPelanggan::whereHas('kelompokPelanggan', function ($query) use ($request) {
            $query->where(
                'id_sekolah',
                $request->user()->id_sekolah
            );
        })
        ->with('kelompokPelanggan')
        ->findOrFail($id);

        return response()->json($pelanggan);
    }

    public function update(Request $request, $id)
    {
        $pelanggan = TbPelanggan::whereHas('kelompokPelanggan', function ($query) use ($request) {
            $query->where(
                'id_sekolah',
                $request->user()->id_sekolah
            );
        })
        ->findOrFail($id);

        $validated = $request->validate([
            'id_kelompok_pelanggan' => 'sometimes|integer',
            'nama_pelanggan' => 'sometimes|string|max:255',
            'telepon' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
        ]);

        if (isset($validated['id_kelompok_pelanggan'])) {
            TbKelompokPelanggan::where(
                'id_kelompok_pelanggan',
                $validated['id_kelompok_pelanggan']
            )
            ->where(
                'id_sekolah',
                $request->user()->id_sekolah
            )
            ->firstOrFail();
        }

        $validated['updated_by'] = $request->user()->id_user;

        $pelanggan->update($validated);

        return response()->json([
            'message' => 'Pelanggan berhasil diubah',
            'data' => $pelanggan
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $pelanggan = TbPelanggan::whereHas('kelompokPelanggan', function ($query) use ($request) {
            $query->where(
                'id_sekolah',
                $request->user()->id_sekolah
            );
        })
        ->findOrFail($id);

        $pelanggan->deleted_by = $request->user()->id_user;
        $pelanggan->is_delete = true;
        $pelanggan->save();

        $pelanggan->delete();

        return response()->json([
            'message' => 'Pelanggan berhasil dihapus'
        ]);
    }
}
