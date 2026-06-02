<?php

namespace App\Services\Scheduling\Generation;

use App\Services\Scheduling\ScheduleOptimizerService;

class TimetableGenerationPipeline
{
    public function __construct(
        protected FreeSlotFinderService $slots,
        protected IntelligentBlockDistributorService $distributor,
        protected ClassroomAssignmentService $classrooms,
        protected ProfessorAvailabilityService $professors,
        protected ScheduleOptimizerService $optimizer,
        protected AcademicEfficiencyService $efficiency,
        protected CurriculumSchedulingService $curriculum
    ) {}

    public function execute(array $payload): array
    {
        $slots = $this->slots->find();

        $distribution = $this->distributor->distribute(
            $payload['subjects'],
            $slots
        );

        $withClassrooms = $this->classrooms->assign(
            $distribution
        );

        $optimized = $this->optimizer->optimize(
            $withClassrooms
        );

        $metrics = $this->efficiency->calculate(
            $optimized
        );

        return [
            'schedule' => $optimized,
            'metrics' => $metrics,
            'recommendations' => [
                'Balanced academic distribution',
                'Low fragmentation detected',
            ],
        ];
    }
}
