<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TbDetailPembelian extends Model
{
    protected $table = 'tb_detail_pembelian';
    protected $primaryKey = 'id_detail_pembelian';

    public $timestamps = false;

    protected $fillable = [
        'id_pembelian',
        'id_barang',
        'satuan',
        'jumlah',
        'harga_beli',
        'subtotal',
    ];

    protected $casts = [
        'jumlah' => 'integer',
        'harga_beli' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function pembelian()
    {
        return $this->belongsTo(
            TbPembelian::class,
            'id_pembelian',
            'id_pembelian'
        );
    }

    public function barang()
    {
        return $this->belongsTo(
            TbBarang::class,
            'id_barang',
            'id_barang'
        );
    }
}