<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Regency extends Model
{
    use HasFactory;

    protected $table = 'regencies';
    protected $primaryKey = 'id';
    public $incrementing = false;
    public $timestamps = true;

    public function province()
    {
        return $this->belongsTo(Province::class, 'province_id', 'id');
    }
}
