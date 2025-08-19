<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LaporanHarian extends Model
{
    use HasFactory;

    protected $table = 'laporan_harian';

    protected $fillable = [
        'tanggal',
        'shift',
        'jam_mulai',
        'jam_selesai',
        'checklist_id',
        'rincian_pekerjaan',
        'area',
        'hasil_pekerjaan',
        'mengetahui',
        'paraf',
        'bukti'
    ];

    public function checklist()
    {
        return $this->belongsTo(Checklist::class, 'checklist_id');
    }
}
