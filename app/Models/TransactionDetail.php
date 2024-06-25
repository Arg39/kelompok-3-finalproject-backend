<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'building_id',
        'room_id',
        'room_name',
        'price',
        'start_date',
        'end_date',
        'duration',
        'sub_total'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
