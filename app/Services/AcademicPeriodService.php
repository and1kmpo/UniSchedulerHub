<?php

namespace App\Services;

use App\Models\AcademicPeriod;
use App\Models\SubjectEnrollment;
use Illuminate\Support\Facades\DB;

class AcademicPeriodService
{
    public function closePeriod(AcademicPeriod $period): void
    {
        DB::transaction(function () use ($period) {

            // 1️⃣ Marcar período como cerrado
            $period->update([
                'is_active' => false,
                'closed_at' => now(),
            ]);

            // 2️⃣ Obtener matrículas pre_enrolled
            $enrollments = SubjectEnrollment::where('academic_period_id', $period->id)
                ->whereHas(
                    'status',
                    fn($q) =>
                    $q->where('code', 'pre_enrolled')
                )
                ->get();

            // 3️⃣ Transicionar una por una (DOMINIO)
            foreach ($enrollments as $enrollment) {
                $enrollment->transitionTo('enrolled');
            }
        });
    }
}
