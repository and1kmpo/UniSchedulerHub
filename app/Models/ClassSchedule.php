<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClassSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['class_group_id', 'day', 'start_time', 'end_time', 'classroom_id'];

    public function getRouteKeyName()
    {
        return 'id';
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    public function classGroup()
    {
        return $this->belongsTo(ClassGroup::class, 'class_group_id');
    }
}
