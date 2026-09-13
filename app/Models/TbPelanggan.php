<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TbPelanggan extends Model
{
    use SoftDeletes;

    protected $table = 'tb_pelanggan';

    protected $primaryKey = 'id_pelanggan';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'id_kelompok_pelanggan',
        'nama_pelanggan',
        'telepon',
        'alamat',
        'created_by',
        'updated_by',
        'deleted_at',
        'deleted_by',
        'is_delete',
    ];

    protected $casts = [
        'is_delete' => 'boolean',
    ];

    public function kelompokPelanggan()
    {
        return $this->belongsTo(
            TbKelompokPelanggan::class,
            'id_kelompok_pelanggan',
            'id_kelompok_pelanggan'
        );
    }
}
