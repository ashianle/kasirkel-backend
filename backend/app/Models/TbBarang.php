<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TbBarang extends Model
{
    use SoftDeletes;

    protected $table = 'tb_barang';
    protected $primaryKey = 'id_barang';

    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'id_sekolah',
        'barcode',
        'nama',
        'id_kategori',
        'id_kelompok_kategori',
        'id_supplier',
        'satuan',
        'harga_beli',
        'harga_jual',
        'stok',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_at',
        'deleted_by',
        'is_delete',
    ];

    protected $casts = [
        'harga_beli' => 'decimal:2',
        'harga_jual' => 'decimal:2',
        'stok' => 'integer',
        'is_active' => 'boolean',
        'is_delete' => 'boolean',
    ];
}