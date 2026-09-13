<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TbKelompokPelanggan extends Model
{
    protected $table = 'tb_kelompok_pelanggan';

    protected $primaryKey = 'id_kelompok_pelanggan';

    public $timestamps = false;

    protected $fillable = [
        'id_sekolah',
        'nama_kelompok',
    ];
}