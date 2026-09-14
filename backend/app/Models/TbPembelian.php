<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TbPembelian extends Model
{
    use SoftDeletes;

    protected $table = 'tb_pembelian';
    protected $primaryKey = 'id_pembelian';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'id_sekolah',
        'id_supplier',
        'id_user',
        'nomor_faktur',
        'tanggal_faktur',
        'total_bayar',
        'status_pembelian',
        'jenis_transaksi',
        'cara_bayar',
        'note',
        'created_by',
        'deleted_at',
        'deleted_by',
        'is_delete',
    ];

    protected $casts = [
        'tanggal_faktur' => 'date',
        'total_bayar' => 'decimal:2',
        'is_delete' => 'boolean',
    ];

    public function supplier()
    {
        return $this->belongsTo(
            TbSupplier::class,
            'id_supplier',
            'id_supplier'
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

    public function detail()
    {
        return $this->hasMany(
            TbDetailPembelian::class,
            'id_pembelian',
            'id_pembelian'
        );
    }
}
