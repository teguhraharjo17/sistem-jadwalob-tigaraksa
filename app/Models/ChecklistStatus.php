<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChecklistStatus extends Model
{
    use HasFactory;

    protected $table = 'checklist_status';

    protected $fillable = [
        'checklist_id',
        'tanggal',
        'shift',
        'status',
    ];
}
