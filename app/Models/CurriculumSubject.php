<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurriculumSubject extends Model
{
    protected $table = 'curriculum_subjects';

    protected $fillable = [
        'curriculum_id',
        'subject_id',
        'semester_recommended',
        'credits',
        'type',
        'area_id',
    ];

    public function curriculum()
    {
        return $this->belongsTo(Curriculum::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function area()
    {
        return $this->belongsTo(SubjectArea::class, 'area_id');
    }
}
