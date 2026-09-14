<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TbDetailPenjualan extends Model
{
    protected $table = 'tb_detail_penjualan';

    protected $primaryKey = 'id_detail_penjualan';

    public $timestamps = false;

    protected $fillable = [
        'id_penjualan',
        'id_barang',
        'jumlah_barang',
        'harga_beli',
        'harga_jual',
        'diskon_tipe',
        'diskon_nilai',
        'diskon_nominal',
        'subtotal',
    ];

    protected $casts = [
        'jumlah_barang' => 'integer',
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'diskon_nilai' => 'decimal:2',
        'diskon_nominal' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function barang()
    {
        return $this->belongsTo(
            TbBarang::class,
            'id_barang',
            'id_barang'
        );
    }

    public function penjualan()
    {
        return $this->belongsTo(
            TbPenjualan::class,
            'id_penjualan',
            'id_penjualan'
        );
    }
}
