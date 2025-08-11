<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $fillable = [
        'name',
        'building_id',
        'floor',
        'capacity',
        'description',
    ];

    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }
}
