<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TbSekolah;
use Illuminate\Http\Request;

class TbSekolahController extends Controller
{
    private function checkSuperAdmin(Request $request)
    {
        if ((int) $request->user()->id_role !== 1) {
            abort(403, 'Hanya superadmin yang boleh mengelola sekolah');
        }
    }

    public function index()
    {
        $sekolah = TbSekolah::orderBy('id_sekolah', 'desc')->get();

        return response()->json($sekolah);
    }

    public function store(Request $request)
    {
        $this->checkSuperAdmin($request);

        $validated = $request->validate([
            'kode_sekolah' => 'required|string|max:50|unique:tb_sekolah,kode_sekolah',
            'nama_sekolah' => 'required|string|max:255',
            'alamat_sekolah' => 'nullable|string',
            'website' => 'nullable|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $sekolah = TbSekolah::create($validated);

        return response()->json([
            'message' => 'Sekolah berhasil ditambahkan',
            'data' => $sekolah
        ], 201);
    }

    public function show($id)
    {
        $sekolah = TbSekolah::findOrFail($id);

        return response()->json($sekolah);
    }

    public function update(Request $request, $id)
    {
        $this->checkSuperAdmin($request);

        $sekolah = TbSekolah::findOrFail($id);

        $validated = $request->validate([
            'kode_sekolah' => 'sometimes|string|max:50|unique:tb_sekolah,kode_sekolah,' . $id . ',id_sekolah',
            'nama_sekolah' => 'sometimes|string|max:255',
            'alamat_sekolah' => 'nullable|string',
            'website' => 'nullable|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        $sekolah->update($validated);

        return response()->json([
            'message' => 'Sekolah berhasil diubah',
            'data' => $sekolah
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $this->checkSuperAdmin($request);

        $sekolah = TbSekolah::findOrFail($id);

        $sekolah->delete();

        return response()->json([
            'message' => 'Sekolah berhasil dihapus'
        ]);
    }
}