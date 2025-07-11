<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClassSchedule extends Model
{
    use HasFactory;

    protected $fillable = ['class_group_id', 'day', 'start_time', 'end_time', 'classroom'];

    public function group()
    {
        return $this->belongsTo(ClassGroup::class, 'class_group_id');
    }
}
