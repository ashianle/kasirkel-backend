<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class TbUser extends Authenticatable
{
    use HasApiTokens, SoftDeletes;

    protected $table = 'tb_user';
    protected $primaryKey = 'id_user';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;
    const DELETED_AT = 'deleted_at';

    protected $fillable = [
        'id_sekolah',
        'id_role',
        'username',
        'password',
        'nama_lengkap',
        'is_active',
        'created_by',
        'updated_by',
        'deleted_at',
        'deleted_by',
        'is_delete',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_delete' => 'boolean',
    ];
}