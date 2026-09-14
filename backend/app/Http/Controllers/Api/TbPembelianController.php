<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TbPembelian;
use App\Models\TbDetailPembelian;
use App\Models\TbBarang;
use App\Models\TbSupplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TbPembelianController extends Controller
{
    public function index(Request $request)
    {
        $pembelian = TbPembelian::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->with(['supplier', 'user', 'detail.barang'])
        ->orderBy('id_pembelian', 'desc')
        ->get();

        return response()->json($pembelian);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_supplier' => 'required|integer',
            'nomor_faktur' => 'required|string|max:100',
            'tanggal_faktur' => 'required|date',
            'status_pembelian' => 'required|in:draft,selesai',
            'jenis_transaksi' => 'required|string|max:50',
            'cara_bayar' => 'nullable|string|max:50',
            'note' => 'nullable|string',

            'detail' => 'required|array|min:1',
            'detail.*.id_barang' => 'required|integer',
            'detail.*.satuan' => 'required|string|max:50',
            'detail.*.jumlah' => 'required|integer|min:1',
        ]);

        $sekolahId = $request->user()->id_sekolah;
        $userId = $request->user()->id_user;

        DB::beginTransaction();

        try {
            TbSupplier::where(
                'id_supplier',
                $validated['id_supplier']
            )
            ->where(
                'id_sekolah',
                $sekolahId
            )
            ->firstOrFail();

            $detailData = [];
            $totalBayar = 0;

            foreach ($validated['detail'] as $item) {
                $barang = TbBarang::where(
                    'id_sekolah',
                    $sekolahId
                )
                ->where(
                    'id_barang',
                    $item['id_barang']
                )
                ->lockForUpdate()
                ->firstOrFail();

                $jumlah = $item['jumlah'];
                $hargaBeli = (float) $barang->harga_beli;
                $subtotal = $hargaBeli * $jumlah;

                $totalBayar += $subtotal;

                $detailData[] = [
                    'id_barang' => $barang->id_barang,
                    'satuan' => $item['satuan'],
                    'jumlah' => $jumlah,
                    'harga_beli' => $hargaBeli,
                    'subtotal' => $subtotal,
                ];

                $barang->stok += $jumlah;
                $barang->save();
            }

            $pembelian = TbPembelian::create([
                'id_sekolah' => $sekolahId,
                'id_supplier' => $validated['id_supplier'],
                'id_user' => $userId,
                'nomor_faktur' => $validated['nomor_faktur'],
                'tanggal_faktur' => $validated['tanggal_faktur'],
                'total_bayar' => $totalBayar,
                'status_pembelian' => $validated['status_pembelian'],
                'jenis_transaksi' => $validated['jenis_transaksi'],
                'cara_bayar' => $validated['cara_bayar'] ?? null,
                'note' => $validated['note'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($detailData as $detail) {
                $detail['id_pembelian'] = $pembelian->id_pembelian;

                TbDetailPembelian::create($detail);
            }

            DB::commit();

            $pembelian->load([
                'supplier',
                'user',
                'detail.barang'
            ]);

            return response()->json([
                'message' => 'Pembelian berhasil dibuat',
                'data' => $pembelian
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Pembelian gagal dibuat',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function show(Request $request, $id)
    {
        $pembelian = TbPembelian::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->with([
            'supplier',
            'user',
            'detail.barang'
        ])
        ->findOrFail($id);

        return response()->json([
            'pembelian' => $pembelian,
            'detail' => $pembelian->detail
        ]);
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $pembelian = TbPembelian::where(
                'id_sekolah',
                $request->user()->id_sekolah
            )
            ->with('detail')
            ->findOrFail($id);

            foreach ($pembelian->detail as $item) {
                $barang = TbBarang::where(
                    'id_sekolah',
                    $request->user()->id_sekolah
                )
                ->where(
                    'id_barang',
                    $item->id_barang
                )
                ->lockForUpdate()
                ->first();

                if (!$barang) {
                    throw new \Exception(
                        'Barang dengan ID ' . $item->id_barang . ' tidak ditemukan'
                    );
                }

                if ($barang->stok < $item->jumlah) {
                    throw new \Exception(
                        'Pembelian tidak bisa dihapus karena stok barang "' .
                        $barang->nama_barang .
                        '" tidak mencukupi untuk mengembalikan stok'
                    );
                }

                $barang->stok -= $item->jumlah;
                $barang->save();
            }

            $pembelian->deleted_by = $request->user()->id_user;
            $pembelian->is_delete = true;
            $pembelian->save();

            $pembelian->delete();

            DB::commit();

            return response()->json([
                'message' => 'Pembelian berhasil dihapus dan stok dikembalikan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Pembelian gagal dihapus',
                'error' => $e->getMessage()
            ], 422);
        }
    }
}