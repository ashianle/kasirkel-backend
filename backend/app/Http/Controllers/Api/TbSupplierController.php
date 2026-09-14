<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TbSupplier;
use Illuminate\Http\Request;

class TbSupplierController extends Controller
{
    public function index(Request $request)
    {
        $supplier = TbSupplier::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->orderBy('id_supplier', 'desc')
        ->get();

        return response()->json($supplier);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => 'required|string|max:255',
            'no_telepon' => 'nullable|string|max:50',
            'alamat_supplier' => 'nullable|string',
        ]);

        $validated['id_sekolah'] = $request->user()->id_sekolah;
        $validated['created_by'] = $request->user()->id_user;

        $supplier = TbSupplier::create($validated);

        return response()->json([
            'message' => 'Supplier berhasil ditambahkan',
            'data' => $supplier
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $supplier = TbSupplier::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )->findOrFail($id);

        return response()->json($supplier);
    }

    public function update(Request $request, $id)
    {
        $supplier = TbSupplier::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )->findOrFail($id);

        $validated = $request->validate([
            'nama' => 'sometimes|string|max:255',
            'no_telepon' => 'nullable|string|max:50',
            'alamat_supplier' => 'nullable|string',
        ]);

        $validated['updated_by'] = $request->user()->id_user;

        $supplier->update($validated);

        return response()->json([
            'message' => 'Supplier berhasil diubah',
            'data' => $supplier
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $supplier = TbSupplier::where(
            'id_supplier',
            $id
        )
        ->where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->findOrFail($id);

        $supplier->deleted_by = $request->user()->id_user;
        $supplier->is_delete = true;
        $supplier->save();

        $supplier->delete();

        return response()->json([
            'message' => 'Supplier berhasil dihapus'
        ]);
    }
}