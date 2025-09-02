<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Checklist extends Model
{
    use HasFactory;

    protected $fillable = [
        'area',
        'pekerjaan',
        'bulan',
        'tahun',
        'start_date',
        'keterangan',
        'frequency_count',
        'frequency_unit',
        'frequency_interval',
        'default_shift',
    ];

    protected $casts = [
        'frequency_count' => 'integer',
        'frequency_interval' => 'integer',
    ];
}
