<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TbUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TbUserController extends Controller
{
    private function checkAdmin(Request $request)
    {
        if (!in_array((int) $request->user()->id_role, [1, 2])) {
            abort(403, 'Hanya admin yang boleh mengelola user');
        }
    }

    private function checkRolePermission(Request $request, $roleId)
    {
        if (
            (int) $roleId === 1 &&
            (int) $request->user()->id_role !== 1
        ) {
            abort(403, 'Admin tidak boleh memberikan role Superadmin');
        }
    }

    public function index(Request $request)
    {
        $user = TbUser::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->orderBy('id_user', 'desc')
        ->get();

        return response()->json($user);
    }

    public function store(Request $request)
    {
        $this->checkAdmin($request);

        $validated = $request->validate([
            'id_role' => 'required|integer|exists:roles,id_role',
            'username' => 'required|string|max:100|unique:tb_user,username',
            'password' => 'required|string|min:6',
            'nama_lengkap' => 'required|string|max:255',
            'is_active' => 'required|boolean',
        ]);

        $this->checkRolePermission(
            $request,
            $validated['id_role']
        );

        $validated['id_sekolah'] = $request->user()->id_sekolah;
        $validated['created_by'] = $request->user()->id_user;
        $validated['password'] = Hash::make($validated['password']);

        $user = TbUser::create($validated);

        return response()->json([
            'message' => 'User berhasil ditambahkan',
            'data' => $user
        ], 201);
    }

    public function show(Request $request, $id)
    {
        $user = TbUser::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->findOrFail($id);

        return response()->json($user);
    }

    public function update(Request $request, $id)
    {
        $this->checkAdmin($request);

        $user = TbUser::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->findOrFail($id);

        $validated = $request->validate([
            'id_role' => 'sometimes|integer|exists:roles,id_role',
            'username' => 'sometimes|string|max:100|unique:tb_user,username,' . $id . ',id_user',
            'password' => 'nullable|string|min:6',
            'nama_lengkap' => 'sometimes|string|max:255',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($validated['id_role'])) {
            $this->checkRolePermission(
                $request,
                $validated['id_role']
            );
        }

        if (
            (int) $request->user()->id_role !== 1 &&
            (int) $user->id_role === 1
        ) {
            abort(403, 'Admin tidak boleh mengubah Superadmin');
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['updated_by'] = $request->user()->id_user;

        $user->update($validated);

        return response()->json([
            'message' => 'User berhasil diubah',
            'data' => $user
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $this->checkAdmin($request);

        $user = TbUser::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->findOrFail($id);

        if (
            (int) $request->user()->id_role !== 1 &&
            (int) $user->id_role === 1
        ) {
            abort(403, 'Admin tidak boleh menghapus Superadmin');
        }

        $user->deleted_by = $request->user()->id_user;
        $user->is_delete = true;
        $user->save();

        $user->delete();

        return response()->json([
            'message' => 'User berhasil dihapus'
        ]);
    }
}