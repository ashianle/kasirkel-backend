<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TbPenjualan extends Model
{
    use SoftDeletes;

    protected $table = 'tb_penjualan';

    protected $primaryKey = 'id_penjualan';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'id_sekolah',
        'id_user',
        'id_pelanggan',
        'tanggal_penjualan',
        'total_faktur',
        'total_bayar',
        'kembalian',
        'status_pembayaran',
        'jenis_transaksi',
        'cara_bayar',
        'note',
        'created_by',
        'deleted_at',
        'deleted_by',
        'is_delete',
    ];

    protected $casts = [
        'tanggal_penjualan' => 'date',
        'total_faktur' => 'decimal:2',
        'total_bayar' => 'decimal:2',
        'kembalian' => 'decimal:2',
        'is_delete' => 'boolean',
    ];

    public function detail()
    {
        return $this->hasMany(
            TbDetailPenjualan::class,
            'id_penjualan',
            'id_penjualan'
        );
    }

    public function pelanggan()
    {
        return $this->belongsTo(
            TbPelanggan::class,
            'id_pelanggan',
            'id_pelanggan'
        );
    }

    public function user()
    {
        return $this->belongsTo(
            TbUser::class,
            'id_user',
            'id_user'
        );
    }
}
