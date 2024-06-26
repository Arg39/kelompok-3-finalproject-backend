<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImageBuilding extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id',
        'image',
    ];

    public function regency()
    {
        return $this->belongsTo(Building::class, 'building_id', 'id');
    }
    protected function image(): Attribute
    {
        return Attribute::make(
            get: function ($image) {
                return url('/storage/buildings/' . $this->building_id . '/' . $image);
            }
        );
    }
}
