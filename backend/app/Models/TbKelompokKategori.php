<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TbKelompokKategori extends Model
{
    use SoftDeletes;

    protected $table = 'tb_kelompok_kategori';
    protected $primaryKey = 'id_kelompok';

    public $timestamps = false;

    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'id_sekolah',
        'nama_kelompok',
        'created_by',
        'deleted_at',
        'deleted_by',
        'is_delete',
    ];

    protected $casts = [
        'is_delete' => 'boolean',
    ];

    public function kategori()
    {
        return $this->hasMany(
            TbKategori::class,
            'id_kelompok',
            'id_kelompok'
        );
    }
}