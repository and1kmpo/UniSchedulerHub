<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GradeStatus extends Model
{
    protected $table = 'grade_statuses';

    public $timestamps = false;

    protected $fillable = ['code', 'label'];

    public function grades()
    {
        return $this->hasMany(Grade::class, 'grade_status_id');
    }
}
