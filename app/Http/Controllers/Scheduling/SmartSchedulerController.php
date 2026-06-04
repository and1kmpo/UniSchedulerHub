<?php

namespace App\Http\Controllers\Scheduling;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Scheduling\Generation\TimetableGeneratorService;
use App\Services\Scheduling\ScheduleOptimizerService;

class SmartSchedulerController extends Controller
{
    public function generate(
        Request $request,
        TimetableGeneratorService $generator
    ) {
        $request->validate([
            'subjects' => ['required', 'array'],
        ]);

        return response()->json(
            $generator->generate(
                $request->all()
            )
        );
    }

    public function optimize(
        Request $request,
        ScheduleOptimizerService $optimizer
    ) {
        $request->validate([
            'schedules' => ['required', 'array'],
        ]);

        $result = $optimizer->optimize(
            $request->schedules
        );

        return response()->json($result);
    }
}
