<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicPeriodStatus extends Model
{
    protected $fillable = [
        'code',
        'name',
        'is_final',
    ];

    public function academicPeriods()
    {
        return $this->hasMany(AcademicPeriod::class);
    }
}
