<?php

namespace App\Http\Controllers\Scheduling;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Scheduling\ScheduleOptimizerService;

class SmartSchedulerController extends Controller
{
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
