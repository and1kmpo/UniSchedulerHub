<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ClassGroup extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_CLOSED = 'closed';

    public const ENROLLABLE_STATUSES = [
        self::STATUS_PUBLISHED,
    ];

    public const SCHEDULE_EDITABLE_STATUSES = [
        self::STATUS_DRAFT,
        self::STATUS_PUBLISHED,
    ];

    protected $fillable = [
        'subject_id',
        'professor_id',
        'semester',
        'name',
        'group_code',
        'academic_period_id',
        'capacity',
        'modality',
        'shift',
        'status',
    ];

    protected static function booted()
    {
        static::creating(function ($group) {
            // Asignar semestre si no se proporciona
            if (empty($group->semester)) {
                $group->semester = self::currentSemester();
            }

            // Generar código de grupo G1, G2, G3... sin saltos
            if (empty($group->group_code)) {
                $existingCodes = self::where('subject_id', $group->subject_id)
                    ->where('semester', $group->semester)
                    ->pluck('group_code') // ['G1', 'G2', 'G4']
                    ->map(fn($code) => (int) str_replace('G', '', $code))
                    ->filter()
                    ->sort()
                    ->values()
                    ->toArray();

                $next = 1;
                foreach ($existingCodes as $used) {
                    if ($used == $next) {
                        $next++;
                    } else {
                        break;
                    }
                }

                $group->group_code = 'G' . $next;
            }

            // Generar código completo del grupo: Ej: MAT101-2025-I-G1
            if (empty($group->code)) {
                $subject = $group->subject ?? \App\Models\Subject::find($group->subject_id);
                $subjectCode = $subject?->code ?? 'SUBJ';
                $group->code = "{$subjectCode}-{$group->semester}-{$group->group_code}";
            }

            // Generar nombre si está vacío
            if (empty($group->name)) {
                $subjectName = $group->subject?->name ?? 'Subject';
                $group->name = "{$subjectName} - {$group->modality} - {$group->shift} - Group {$group->group_code}";
            }
        });
    }

    public static function currentSemester(): string
    {
        $year = now()->year;
        $period = now()->month <= 6 ? 'I' : 'II';
        return "{$year}-{$period}";
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class)->withTrashed();
    }

    public function professor()
    {
        return $this->belongsTo(User::class, 'professor_id');
    }

    public function professorProfile()
    {
        return $this->belongsTo(Professor::class, 'professor_id', 'user_id');
    }


    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function subjectEnrollments()
    {
        return $this->hasMany(SubjectEnrollment::class);
    }

    public function students()
    {
        return $this->hasManyThrough(Student::class, SubjectEnrollment::class, 'class_group_id', 'id', 'id', 'student_id');
    }

    public function academicPeriod()
    {
        return $this->belongsTo(AcademicPeriod::class);
    }

    public function isPublished(): bool
    {
        return in_array($this->status, self::ENROLLABLE_STATUSES, true);
    }

    public function canManageSchedules(): bool
    {
        return in_array($this->status, self::SCHEDULE_EDITABLE_STATUSES, true);
    }
}
