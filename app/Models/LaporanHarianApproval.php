<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaporanHarianApproval extends Model
{
    protected $table = 'laporan_harian_approval';

    protected $fillable = ['bulan', 'tahun', 'nama', 'ttd_path'];
}
