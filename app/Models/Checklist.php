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
        'periodic_cleaning',
        'bulan',
        'tahun',
        'keterangan',
    ];
}
