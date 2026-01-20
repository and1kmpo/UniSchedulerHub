<?php

namespace App\Console\Commands;

use App\Models\AcademicPeriod;
use App\Services\AcademicPeriodService;
use Illuminate\Console\Command;

class CloseAcademicPeriod extends Command
{

    protected $signature = 'academic-period:close {periodId}';
    protected $description = 'Closes an academic period and confirms enrollments';


    public function handle(AcademicPeriodService $service): int
    {
        $period = AcademicPeriod::find($this->argument('periodId'));

        if (!$period) {
            $this->error('Academic period not found.');
            return Command::FAILURE;
        }

        if (!$period->is_active) {
            $this->warn('Academic period is already closed.');
            return Command::SUCCESS;
        }

        $service->closePeriod($period);

        $this->info('Academic period closed successfully.');
        return Command::SUCCESS;
    }
}
