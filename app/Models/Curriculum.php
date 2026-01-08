<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Curriculum extends Model
{
    protected $fillable = [
        'program_id',
        'code',
        'name',
        'valid_from',
        'valid_to',
        'is_active',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to'   => 'date',
        'is_active'  => 'boolean',
    ];

    /* =========================
     |  Relationships
     ========================= */

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function subjects()
    {
        return $this->belongsToMany(Subject::class, 'curriculum_subjects')
            ->withPivot([
                'semester_recommended',
                'credits',
                'type',
                'area_id',
            ])
            ->withTimestamps();
    }

    protected static function booted()
    {
        static::saving(function ($curriculum) {
            if ($curriculum->is_active) {
                self::where('program_id', $curriculum->program_id)
                    ->where('id', '!=', $curriculum->id)
                    ->update(['is_active' => false]);
            }
        });
    }
}
