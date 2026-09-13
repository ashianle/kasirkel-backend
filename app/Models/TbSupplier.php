<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TbSupplier extends Model
{
    use SoftDeletes;

    protected $table = 'tb_supplier';
    protected $primaryKey = 'id_supplier';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'id_sekolah',
        'nama',
        'no_telepon',
        'alamat_supplier',
        'created_by',
        'updated_by',
        'deleted_at',
        'deleted_by',
        'is_delete',
    ];

    protected $casts = [
        'is_delete' => 'boolean',
    ];
}