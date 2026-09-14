<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TbPenjualan;
use App\Models\TbDetailPenjualan;
use App\Models\TbBarang;
use App\Models\TbPelanggan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TbPenjualanController extends Controller
{
    public function index(Request $request)
    {
        $penjualan = TbPenjualan::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->with('pelanggan')
        ->orderBy('id_penjualan', 'desc')
        ->get();

        return response()->json($penjualan);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_pelanggan' => 'nullable|integer',
            'tanggal_penjualan' => 'required|date',
            'total_bayar' => 'required|numeric|min:0',
            'status_pembayaran' => 'required|in:sudah bayar,belum bayar',
            'jenis_transaksi' => 'required|string|max:50',
            'cara_bayar' => 'required|string|max:50',
            'note' => 'nullable|string',

            'detail' => 'required|array|min:1',
            'detail.*.id_barang' => 'required|integer',
            'detail.*.jumlah_barang' => 'required|integer|min:1',
        ]);

        $sekolahId = $request->user()->id_sekolah;
        $userId = $request->user()->id_user;

        DB::beginTransaction();

        try {
            if (!empty($validated['id_pelanggan'])) {
                TbPelanggan::whereHas('kelompokPelanggan', function ($query) use ($sekolahId) {
                    $query->where('id_sekolah', $sekolahId);
                })
                ->findOrFail($validated['id_pelanggan']);
            }

            $detailData = [];
            $totalFaktur = 0;

            foreach ($validated['detail'] as $item) {
                $barang = TbBarang::where(
                    'id_sekolah',
                    $sekolahId
                )
                ->where('id_barang', $item['id_barang'])
                ->lockForUpdate()
                ->firstOrFail();

                $jumlah = $item['jumlah_barang'];

                if ($barang->stok < $jumlah) {
                    throw new \Exception(
                        "Stok barang {$barang->nama} tidak mencukupi"
                    );
                }

                $hargaBeli = (float) $barang->harga_beli;
                $hargaJual = (float) $barang->harga_jual;
                $subtotal = $hargaJual * $jumlah;

                $totalFaktur += $subtotal;

                $detailData[] = [
                    'id_barang' => $barang->id_barang,
                    'jumlah_barang' => $jumlah,
                    'harga_beli' => $hargaBeli,
                    'harga_jual' => $hargaJual,
                    'diskon_tipe' => null,
                    'diskon_nilai' => 0,
                    'diskon_nominal' => 0,
                    'subtotal' => $subtotal,
                ];

                $barang->stok -= $jumlah;
                $barang->save();
            }

            $totalBayar = (float) $validated['total_bayar'];
            $kembalian = max($totalBayar - $totalFaktur, 0);

            $penjualan = TbPenjualan::create([
                'id_sekolah' => $sekolahId,
                'id_user' => $userId,
                'id_pelanggan' => $validated['id_pelanggan'] ?? null,
                'tanggal_penjualan' => $validated['tanggal_penjualan'],
                'total_faktur' => $totalFaktur,
                'total_bayar' => $totalBayar,
                'kembalian' => $kembalian,
                'status_pembayaran' => $validated['status_pembayaran'],
                'jenis_transaksi' => $validated['jenis_transaksi'],
                'cara_bayar' => $validated['cara_bayar'],
                'note' => $validated['note'] ?? null,
                'created_by' => $userId,
            ]);

            foreach ($detailData as $detail) {
                $detail['id_penjualan'] = $penjualan->id_penjualan;

                TbDetailPenjualan::create($detail);
            }

            DB::commit();

            $penjualan->load([
                'pelanggan',
                'user',
                'detail.barang',
            ]);

            return response()->json([
                'message' => 'Penjualan berhasil dibuat',
                'data' => $penjualan
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Penjualan gagal dibuat',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    public function show(Request $request, $id)
    {
        $penjualan = TbPenjualan::where(
            'id_sekolah',
            $request->user()->id_sekolah
        )
        ->with([
            'pelanggan',
            'user',
            'detail.barang',
        ])
        ->findOrFail($id);

        return response()->json([
            'penjualan' => $penjualan,
            'detail' => $penjualan->detail
        ]);
    }

    public function destroy(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $penjualan = TbPenjualan::where(
                'id_sekolah',
                $request->user()->id_sekolah
            )
            ->findOrFail($id);

            $detail = TbDetailPenjualan::where(
                'id_penjualan',
                $id
            )->get();

            foreach ($detail as $item) {
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

                $barang->stok += $item->jumlah_barang;
                $barang->save();
            }

            $penjualan->deleted_by = $request->user()->id_user;
            $penjualan->is_delete = true;
            $penjualan->save();

            $penjualan->delete();

            DB::commit();

            return response()->json([
                'message' => 'Penjualan berhasil dihapus dan stok dikembalikan'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Penjualan gagal dihapus',
                'error' => $e->getMessage()
            ], 422);
        }
    }
}