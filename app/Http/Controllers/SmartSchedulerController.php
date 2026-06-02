<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\Scheduling\Generation\TimetableGeneratorService;

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
}
