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
        'status'
    ];

    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function building()
    {
        return $this->belongsTo(Building::class);
    }


    public function classGroups()
    {
        return $this->hasManyThrough(
            ClassGroup::class,       // Modelo final
            ClassSchedule::class,    // Modelo intermedio
            'classroom_id',          // FK en ClassSchedule -> Classroom
            'id',                    // PK en ClassGroup
            'id',                    // PK en Classroom
            'class_group_id'         // FK en ClassSchedule -> ClassGroup
        );
    }
}
