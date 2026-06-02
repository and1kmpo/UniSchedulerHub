<?php

namespace App\Services\Scheduling;

class ScheduleOptimizerService
{
    public function __construct(
        protected ConflictDetectionService $conflictDetector,
        protected ScheduleScoreService $scoreService,
        protected ClassroomOptimizationService $classroomOptimizer,
        protected ProfessorLoadBalancerService $professorBalancer,
        protected SmartRecommendationService $recommendationService,
    ) {}

    public function optimize(array $schedules): array
    {
        /*
        |--------------------------------------------------------------------------
        | Detect conflicts
        |--------------------------------------------------------------------------
        */

        $conflicts = $this->conflictDetector
            ->detect($schedules);

        /*
        |--------------------------------------------------------------------------
        | Classroom optimization
        |--------------------------------------------------------------------------
        */

        $classroomOptimization = $this->classroomOptimizer
            ->optimize($schedules);

        /*
        |--------------------------------------------------------------------------
        | Professor balancing
        |--------------------------------------------------------------------------
        */

        $professorBalancing = $this->professorBalancer
            ->analyze($schedules);

        /*
        |--------------------------------------------------------------------------
        | Score engine
        |--------------------------------------------------------------------------
        */

        $score = $this->scoreService
            ->calculate([
                'schedules' => $schedules,
                'conflicts' => $conflicts,
                'classrooms' => $classroomOptimization,
                'professors' => $professorBalancing,
            ]);

        /*
        |--------------------------------------------------------------------------
        | Smart recommendations
        |--------------------------------------------------------------------------
        */

        $recommendations = $this->recommendationService
            ->generate([
                'conflicts' => $conflicts,
                'score' => $score,
                'classrooms' => $classroomOptimization,
                'professors' => $professorBalancing,
            ]);

        return [
            'conflicts' => $conflicts,
            'score' => $score,
            'classrooms' => $classroomOptimization,
            'professors' => $professorBalancing,
            'recommendations' => $recommendations,
        ];
    }
}
