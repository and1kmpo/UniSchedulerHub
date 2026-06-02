<?php

namespace App\Services\Scheduling\Generation;

use App\Services\Scheduling\ScheduleOptimizerService;

class TimetableGeneratorService
{
    public function __construct(
        protected FreeSlotFinderService $slotFinder,
        protected IntelligentBlockDistributorService $distributor,
        protected ClassroomAssignmentService $classrooms,
        protected ScheduleOptimizerService $optimizer,
        protected AcademicEfficiencyService $efficiency,
        protected TimetableGenerationPipeline $pipeline
    ) {}

    public function generate(array $payload): array
    {
        return $this->pipeline->execute($payload);
    }
}
