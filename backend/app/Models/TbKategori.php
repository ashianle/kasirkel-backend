<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TbKategori extends Model
{
    use SoftDeletes;

    protected $table = 'tb_kategori';

    protected $primaryKey = 'id_kategori';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'id_kelompok',
        'nama',
        'created_by',
        'updated_by',
        'deleted_at',
        'deleted_by',
        'is_delete',
    ];

    protected $casts = [
        'is_delete' => 'boolean',
    ];

    public function kelompokKategori()
    {
        return $this->belongsTo(
            TbKelompokKategori::class,
            'id_kelompok',
            'id_kelompok'
        );
    }
}